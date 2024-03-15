<?php

namespace Northrook\Symfony\Core;

// todo: Create northrook/symfony-asset-manager-bundle
// OR
// as part of Components?
// sort has to be integrated into Symfony proper
//

/** # No, asset management should be part of core
 *
 * We can have a check when calling Component Assets, like icons, images, etc.
 *
 * I the ::class exists, we class:getAssets() an array of assets.
 *
 * This will almost exclusively be icons/vectors.
 *
 * * Load and cache assets
 * * Preprocess assets
 * * Loads svg, images, css, js, etc
 * * Provides a type for each, with front-end print(), and separate properties
 * * Contains special functionality per type, scaling/focal-point for images, line-width for svg, etc
 *
 * Do we really need a dedicated asset manager for everything?
 * I'd mostly likely not want to be using a $this->asset->svg( 'icon' )
 * Rather something like  new Icon( 'icon' ), and it fetches
 * Same with Images, as new Image( 'image' )
 * Maybe create the asst manager, and inject it into each component via setter?
 * Or just regular DI, as we really only need the Filesystem, and can use File::path()
 * Neither of which require container injection
 *
 * Put all these in northrook\symfony-components-bundle
 *
 */
final class Assets extends SymfonyCoreFacade
{

}