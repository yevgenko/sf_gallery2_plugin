<?php

class sfGallery2Routing
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    $embed_uri = sfConfig::get('app_gallery2_embed_uri');
    // Preprend our route
    $r->prependRoute('sf_gallery2_plugin', $embed_uri.'/:action', array('module' => 'sfGallery2Plugin', 'action' => 'index'));
  }
}
