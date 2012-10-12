<?php
namespace DrupalComposerRepo\Console;

use Goutte\Client;

class Application {
    public function run() {
        $this->googleSearch();        
    }

    public function googleSearch() {
        $pages = 1;
        $terms = 'site:drupalcode.org "composer.json" "drupal-module"';
        $url = 'http://www.google.com/search?' . http_build_query(array('q' => $terms));
        var_dump($url);

        // Request search results:
        $client = new Client;
        $crawler = $client->request('GET', $url);

        // See response content:
        // $response = $client->getResponse();
        // $response->getContent();

        // Start crawling the search results:
        $page = 1;
        $result = null;

        while (is_null($result) || $page <= $pages) {
            // If we are moving to another page then click the paging link:
            if ($page > 1) {
                $link = $crawler->selectLink($page)->link();
                $crawler = $client->click($link);
          }

          // Use a CSS filter to select only the result links:
          $links = $crawler->filter('li h3 a');
          
          if ($links->count())
          {
              $links->text();
          }

          // Search the links for the domain:
          foreach ($links as $index => $link) { 
              $href = $link->getAttribute('href');
              var_dump($href);
              $result = ($index + 1) + (($page - 1) * 10);
          }

          $page++;
    }  

        var_dump($result);
    }
}
