<?php

$this->dispatcher->connect('routing.load_configuration', array('sfGallery2Routing', 'listenToRoutingLoadConfigurationEvent'));
