<?php

/**
 * sfGallery2Plugin actions.
 *
 * @package    sfGallery2Plugin
 * @author     Florian Rey aka nerVo <qrf_nervo[dot]net[at]gmail[dot]com>
 * @since      1.0.0 - 16 may 2007
 */

class sfGallery2PluginActions extends sfActions
{
  /**
   * index action
   * 
   */
  public function executeIndex()
  {
    // Prevent PHP to show some "non standards" gallery2 errors in dev mode
    @error_reporting(E_ALL);
	
    // Initiate Gallery2
    require_once(sfConfig::get('app_gallery2_dir').'/embed.php'); 
    $ret = GalleryEmbed::init(array(
      'g2Uri' => '/'.sfConfig::get('app_gallery2_uri'),
      'embedUri' => sfConfig::get('app_gallery2_embed_uri'),
      'loginRedirect' => sfConfig::get('app_gallery2_login_redirect', null)
    ));

    // Get Map Mode
    $map_mode = sfConfig::get('app_gallery2_map_mode',0);

    // Assume user is guest 
    $user_id = '';
	
    // Is user authenticated ?
    if (($this->getUser()->isAuthenticated()) && ($map_mode))
    {
      switch($map_mode)
      {
        case 1:
          $sf_user_id = '1';
          $g2_user_name = sfConfig::get('app_gallery2_map_user', null);
          break;
        case 2:
          if (($this->getUser()->getGuardUser()) && ($this->getUser()->getGuardUser()->getIsSuperAdmin()))
          {
            $sf_user_id = '2';
            $g2_user_name = sfConfig::get('app_gallery2_map_admin', null);
          }
          else
          {
            $sf_user_id = '1';
            $g2_user_name = sfConfig::get('app_gallery2_map_user', null);
          }
          break;
      }

      list($ret,$g2_user) = GalleryCoreApi::fetchUserByUserName($g2_user_name);
	
      if (!$ret)
      {
        $g2_user_id = $g2_user->getId();
					
        // Check if $sf_user_id is well mapped to the wanted g2 user
        list($ret,$map_array) = GalleryEmbed::getExternalIdMap('externalId');

        if ((!$ret) && ($map_array[$sf_user_id]['entityId'] == $g2_user_id))
        {
          $user_id = $sf_user_id;
        }
        else
        {
          // There may be a map collision, so delete all map
          $ret = GalleryCoreApi::removeAllMapEntries('ExternalIdMap');

          if (!GalleryEmbed::addExternalIdMapEntry($sf_user_id, $g2_user_id, 'GalleryUser'))
          {
            $user_id = $sf_user_id;
          }
        }
      } 
    }

    // Set active user
    GalleryEmbed::checkActiveUser($user_id);

    // Allow "connection" link
    if (sfConfig::get('app_gallery2_login',0))
		$gallery->setConfig('login', true);
	
    // If error :
    // - dev mode : get error as html, and show it
    // - prod mode : forward to error 404 page 
    if ($ret)
    {
      if (SF_ENVIRONMENT == 'dev')
      {
        $this->data = $ret->getAsHtml();
        return sfView::SUCCESS;
      }
      else
        $this->forward404();
    }

    // User interface: you could disable sidebar in G2 and get it as separate HTML to put it into a block
    if (!sfConfig::get('app_gallery2_show_sidebar_blocks',1))
      GalleryCapabilities::set('showSidebarBlocks', false);
    
    // handle the G2 request
    // If its image, script stop here
    $g2moddata = GalleryEmbed::handleRequest();

    // If error (isDone is not defined):
    // - dev mode : get error as html, and show it
    // - prod mode : forward to error 404 page 
    if (!isset($g2moddata['isDone']))
    {
      if (SF_ENVIRONMENT == 'dev')
      {
        $this->data = 'Gallery2 : isDone is not defined.';
        return sfView::SUCCESS;
      }
      else
        $this->forward404();
    }

    // exit properly if it was an immediate view / request (G2 already outputted some data)
    if ($g2moddata['isDone']) {
      exit;
    }

    // Parse CSS files
    preg_match_all('@href="(.*).css"@i', $g2moddata['headHtml'], $files);    
    foreach($files[1] as $file)
      $this->getResponse()->addStyleSheet($file);

    // Parse JS files
    preg_match_all('@src="(.*).js"@i', $g2moddata['headHtml'], $files);    
    foreach($files[1] as $file)
      $this->getResponse()->addJavascript($file);

    // Parse title
    preg_match_all('@<title>(.*)</title>@i', $g2moddata['headHtml'], $title);
    if ((isset($title[1][0])) && (!empty($title[1][0])))
      $this->getResponse()->setTitle($title[1][0]);

    $this->data = $g2moddata;
  }
}
