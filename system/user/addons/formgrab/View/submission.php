<div class="box mb">
	<div class="tbl-ctrls">
	<div class="md-wrap">

		<fieldset class="tbl-search right">
			<a class="btn tn action" href="<?=ee('CP/URL')->make('addons/settings/formgrab/index/'.$form_id)?>">Back</a>
		</fieldset>

		<h1>Submission data</h1>
		<div class="app-notice-wrap"><?=ee('CP/Alert')->getAllInlines()?></div>

		<dl class="formgrab-submission-list">
			<?php foreach ($items as $item => $value): ?>
				<dt><?=lang($item)?></dt>
				<dd><?=($value)?:'&mdash;'?></dd>
			<?php endforeach; ?>
		</dl>
	</div>
	</div>
</div>

<style>
	.formgrab-submission-list {
		margin: 0; padding: 0;
		line-height: 1.5;
	}
	.formgrab-submission-list dt {
		margin: 1em 0 0.5em 0; padding: 0;
		font-weight: bold;
		font-size: 18px;
	}
	.formgrab-submission-list dd {
		margin: 0; padding: 0;
	}
</style>