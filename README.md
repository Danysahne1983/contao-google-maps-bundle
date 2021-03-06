# Contao Google Maps Bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-google-maps-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-google-maps-bundle.svg)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-google-maps-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-google-maps-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-google-maps-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-google-maps-bundle?branch=master)

This bundle offers functionality concerning the Google Maps v3 API. It acts as a contao wrapper for [egeloen/ivory-google-map](https://github.com/egeloen/ivory-google-map) and [egeloen/IvoryGoogleMapBundle](https://github.com/egeloen/IvoryGoogleMapBundle).

*NOTE: This bundle is in beta state and not fully implemented, yet. Refer to TODO section for more details about missing features*

## Features

- introduces a simple Contao backend entity to configure your Google Map and overlays (markers, info windows, ...)
- frontend module and content element
- insert tag and twig function
- easy contao command based migration tool for [delahaye/dlh_googlemaps](https://github.com/delahaye/dlh_googlemaps) (courtesy to delahaye!)
- responsive support (mobile first), provide responsive configurations that will update the map upon reaching the value (greater than breakpoint)    

## Installation

0. Optional: If you have already google maps created with [delahaye/dlh_googlemaps](https://github.com/delahaye/dlh_googlemaps) refer to the section "Migrating from dlh_googlemaps".
1. Set your Google API key (capable of Google Maps and Google Static Maps) if not already done in one of the following places (ascending priority):
    - global Contao settings (`tl_settings`)
    - page root (`tl_page`)
    - Google Maps config (`tl_google_map`)
2. Create a Google Map using the corresponding menu entry in Contao on the left.
3. If necessary, create also overlays like markers, info windows, ... in the Google Map defined in 2. Think of a Google Map as an archive of overlays.
4. Now you can integrate the map in your website using one of the following bukt-in ways:
    - Content element
    - Module
    - Insert tag (see below)
    - Twig function (see below)

## Migrating from dlh_googlemaps

_NOTE: For migration you have to have [delahaye/dlh_googlemaps](https://github.com/delahaye/dlh_googlemaps) installed._

Although we cannot guarantee to fully migrate your existing dlh_googlemaps instances, you will nevertheless have a point to start from. Think of it as a 95% migration ;-)

Migrating is as simple as running `vendor/bin/contao-console huh:google-maps:migrate-dlh` from your contao root dir. Your dlh google maps are not changed by this process, only new instances in `tl_google_map` and `tl_google_map_overlay` are created out of the existing legacy data.

## Insert Tags

Name | Arguments | Example
---- | --------- | -------
google_map | ID of the `tl_google_map` instance | {{google_map::1}}
google_map_html | ID of the `tl_google_map` instance | {{google_map_html::1}}
google_map_css | ID of the `tl_google_map` instance | {{google_map_css::1}}
google_map_js | ID of the `tl_google_map` instance | {{google_map_js::1}}

## Twig functions

Name | Arguments | Example
---- | --------- | -------
google_map | ID of the `tl_google_map` instance | {{ google_map(1) }}
google_map_html | ID of the `tl_google_map` instance | {{ google_map_html(1) }}
google_map_css | ID of the `tl_google_map` instance | {{ google_map_css(1) }}
google_map_js | ID of the `tl_google_map` instance | {{ google_map_js(1) }}

## Events

Name | Arguments | Description
---- | --------- | -----------
DlhMigrationModifyMapEvent | $legacyMap, $map | Triggered just before a the migrated map is being saved
DlhMigrationModifyOverlayEvent | $legacyOverlay, $overlay, $legacyMap, $map | Triggered just before a the migrated overlay is being saved

## TODO

- Overlay types:
    - polyline
    - polygon
    - circle
    - rectangle
    - ground_overlay