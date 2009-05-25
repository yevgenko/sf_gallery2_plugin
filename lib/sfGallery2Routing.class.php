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
    // prepend our routes
    if (class_exists('sfRoute')) // Symfony 1.1 compatibility. Checks for sfRoute because it is not implemented in 1.1
    {
      $r->prependRoute('sf_gallery2_plugin', new sfRoute($embed_uri.'/:action', array('module' => 'sfGallery2Plugin', 'action' => 'index')));
    }
    else
    {
      $r->prependRoute('sf_gallery2_plugin', $embed_uri.'/:action', array('module' => 'sfGallery2Plugin', 'action' => 'index'));
    }

  }
}
