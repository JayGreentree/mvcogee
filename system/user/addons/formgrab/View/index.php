
<div class="box">
<div class="tbl-ctrls">

	<?=form_open('')?>

		<?php if( isset( $form ) && ! is_null( $form ) ): ?>
		<fieldset class="tbl-search right">
			<a class="btn tn action" href="<?=ee('CP/URL')->make('addons/settings/formgrab/export/'.$form->form_id)?>">Export</a>
		</fieldset>
		<?php endif ?>

		<h1><?=$cp_page_title?></h1>
		<div class="app-notice-wrap"><?=ee('CP/Alert')->getAllInlines()?></div>

		<?php if( isset( $form ) && ! is_null( $form ) ): ?>

		<?php if (isset($filters)) echo $filters; ?>

		<?php $this->embed('ee:_shared/table', $table); ?>
		<?php if (isset($pagination)) echo $pagination; ?>

		<?php else: ?>

			<div class="tbl-list-wrap">
				<ul class="tbl-list">
					<li><?= lang('formgrab_no_forms') ?></li>
				</ul>
			</div>

		<?php endif ?>

		<fieldset class="tbl-bulk-act hidden">
			<select name="bulk_action">
				<option>-- <?=lang('with_selected')?> --</option>
				<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove"><?=lang('remove')?></option>
				<option value="new"><?=lang('mark_as_new')?></option>
				<option value="read"><?=lang('mark_as_read')?></option>
				<option value="archived"><?=lang('mark_as_archived')?></option>
			</select>
			<input class="btn submit" data-conditional-modal="confirm-trigger" type="submit" value="<?=lang('submit')?>">
		</fieldset>

	</form>
</div>
</div>

<?php

if( isset( $form ) ) {
	$modal_vars = array(
		'name'		=> 'modal-confirm-remove',
		'form_url'	=> ee('CP/URL')->make('addons/settings/formgrab/submission_remove'),
		'hidden'	=> array(
			'bulk_action'	=> 'remove',
			'form_id'	=> $form->form_id
		)
	);

	$modal = $this->make('ee:_shared/modal_confirm_remove')->render($modal_vars);
	ee('CP/Modal')->addModal('remove', $modal);
}

?>

