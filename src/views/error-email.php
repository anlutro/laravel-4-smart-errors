<p>
	<strong>Uncaught exception:</strong> <?php echo $exception->renderInfoHtml(); ?>
</p>

<p><b>Application information</b></p>
<p>
	<?php echo $info->renderHtml(); ?>
</p>

<p><b>Exception stack trace</b></p>
<p><pre style="white-space:pre-wrap;"><?php echo $exception->renderTraceHtml(); ?></pre></p>

<?php if ($exception->previous): ?>
<p>
	<b>Previous exception:</b> <?php echo $exception->previous->renderInfoHtml(); ?>
</p>
<?php endif; ?>

<?php if ($input): ?>
<hr>
<p>
	<b>Input</b><br>
	<p><?php echo $input->renderHtml(); ?></p>
</p>
<?php endif; ?>

<?php if ($queryLog): ?>
<hr>
<p>
	<b>Query log</b><br>
	<?php echo $queryLog->renderHtml(); ?>
</p>
<?php endif; ?>
