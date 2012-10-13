<?php
namespace DrupalComposerRepo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

use Composer\Composer;
use Composer\Config;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\AliasPackage;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\PackageInterface;
use Composer\Json\JsonFile;
use Composer\Satis\Satis;

use Goutte\Client;
use Guzzle\Common\Collection;
use Guzzle\Http\QueryString;

/**
 * Crawl Google for composer projects on Drupal.org.
 */
class CrawlCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('crawl')
        ->setDescription('Crawl for Composer Packages')
        ;
    }

    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pages = 25;
        $page = 1;


        $output->writeln('<info>Beginning Google crawl</info>');

        $query = new QueryString(array('q' => 'site:drupalcode.org "composer.json" "drupal-module"'));
        $url = 'http://www.google.com/search?' . $query;

        // Load page 1
        $client = new Client;
        $crawler = $client->request('GET', $url);

        $repos = array();

        // Crawl through search pages.
        do {
            $current = $client->getHistory()->current()->getUri();
            $output->writeln('<info>Crawling:</info> ' . $current);

            // Use a CSS filter to select only the result links:
            $links = $crawler->filter('li h3 a');

            // Search the links for the domain:
            foreach ($links as $index => $link) {
                $href = $link->getAttribute('href');

                $query = QueryString::fromString(parse_url($href, PHP_URL_QUERY));
                $url = $query->get('q');

                // Match pages with composer.json in root.
                if (preg_match('/^http:\/\/drupalcode.org.+\.git\/.+\/composer.json$/i', $url)) {
                    // Strip to git url and rewrite to drupalcode.org then store unique matches.
                    $matches = array();
                    preg_match('/^http:\/\/drupalcode.org.+\.git/i', $url, $matches);
                    $repo = str_replace('http://drupalcode.org/', 'http://git.drupal.org/', $matches[0]);
                    $repos[$repo] = null;
                    $output->writeln('<info>Found:</info> ' . $repo);
                }
            }

            // Turn the page.
            $page++;
            $node = $crawler->filter('table#nav')->selectLink($page);
            if ($node->count()) {
                $crawler = $client->click($node->link());
            }
            else {
                break;
            }

        } while ($page < $pages);

        $path = getcwd() . '/satis.json';
        $file = new JsonFile($path);
        $data = $file->read();

        foreach ($data['repositories'] as $file_repo) {
            $repos[$file_repo['url']] = null;
        }

        $repos = array_keys($repos);
        sort($repos);

        $data['repositories'] = array();
        foreach ($repos as $repo) {
            $data['repositories'][] = array(
                    'url' => $repo,
                    'type' => 'vcs',
            );
        }

        $file->write((array) $data);
    }
}