<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\GoogleMapsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\GoogleMapsBundle\HeimrichHannotGoogleMapsBundle;
use Ivory\GoogleMapBundle\IvoryGoogleMapBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        $loadAfterBundles = [ContaoCoreBundle::class, IvoryGoogleMapBundle::class];

        if (class_exists('HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle')) {
            $loadAfterBundles[] = 'HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle';
        }

        if (class_exists('HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle')) {
            $loadAfterBundles[] = 'HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle';
        }

        return [
            BundleConfig::create(IvoryGoogleMapBundle::class)->setLoadAfter([ContaoCoreBundle::class]),
            BundleConfig::create(HeimrichHannotGoogleMapsBundle::class)->setLoadAfter($loadAfterBundles),
        ];
    }
}
