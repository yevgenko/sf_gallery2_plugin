<?php 
  if (isset($data['sidebarBlocksHtml']) && !empty($data['sidebarBlocksHtml']))
    echo $data['sidebarBlocksHtml'];

  if (isset($data['bodyHtml']) && !empty($data['bodyHtml']))
    echo $data['bodyHtml'];
?>
