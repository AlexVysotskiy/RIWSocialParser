<?php

namespace Parser\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseNewPostsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('parser:parse_new_posts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = array('parser_service.instagram', 'parser_service.twitter');

        $container = $this->getContainer();
        foreach ($services as $service) {

            if ($parser = $container->get($service)) {

                $parser->parse();
            }
        }
    }

}
