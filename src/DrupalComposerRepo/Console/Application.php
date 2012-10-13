<?php
namespace DrupalComposerRepo\Console;

use Composer\Json\JsonFile;
use Composer\Satis\Console\Application as SatisApplication;

use DrupalComposerRepo\Command\CrawlCommand;

use Goutte\Client;
use Guzzle\Common\Collection;
use Guzzle\Http\QueryString;

class Application extends SatisApplication{

    /**
     * Initializes all the composer commands
     */
    protected function registerCommands()
    {
      $this->add(new CrawlCommand());
      parent::registerCommands();
    }
}
