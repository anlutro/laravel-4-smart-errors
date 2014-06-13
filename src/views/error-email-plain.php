Uncaught exception: <?php echo $exception->renderInfoPlain(); ?>


Application information
=======================
<?php echo $info->renderPlain(); ?>


Exception stack trace
=====================
<?php echo $exception->renderTracePlain(); ?>
<?php if ($exception->previous): ?>


Previous exception
==================
<?php echo $exception->previous->renderInfoPlain(); ?>
<?php endif; ?>
<?php if ($input): ?>


Input
=====
<?php echo $input->renderPlain(); ?>
<?php endif; ?>
<?php if ($queryLog): ?>


Query Log
=========
<?php echo $queryLog->renderPlain(); ?>
<?php endif; ?>
