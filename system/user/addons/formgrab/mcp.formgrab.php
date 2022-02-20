<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package     ExpressionEngine
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2003 - 2018, EllisLab, Inc.
 * @license     http://expressionengine.com/user_guide/license.html
 * @link        http://expressionengine.com
 * @since       Version 2.0
 * @filesource
 */

/**
 * FormGrab Module Control Panel File
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Module
 * @author     Andrew Weaver
 * @link       http://brandnewbox.co.uk/
 */

use EllisLab\ExpressionEngine\Library\CP\Table;

class Formgrab_mcp
{
	public function index( $form_id = null )
	{
		$this->base_url = ee('CP/URL')->make('addons/settings/formgrab/index/');

		$formService = ee('formgrab:Form');

		if( ee()->input->post('bulk_action') == "new" ) {
			$formService->markSubmissions( ee()->input->post('submissions'), 'new');
		}
		if( ee()->input->post('bulk_action') == "read" ) {
			$formService->markSubmissions( ee()->input->post('submissions'), 'read');
		}
		if( ee()->input->post('bulk_action') == "archived" ) {
			$formService->markSubmissions( ee()->input->post('submissions'), 'archived');
		}

		// Find $form_id or use first form
		if( $form_id == null ) {
			$form = $formService->all()->first();
			if( is_null( $form ) ) {
				$form_id = null;
			} else {
				$form_id = $form->form_id;
			}
		} else {
			$form = $formService->getById( $form_id );
		}

		$data = array();

		if( is_null( $form ) ) {

			$data["cp_page_title"] = lang('formgrab_module_name');
			$data["form"] = null;

			$this->generateSidebar();

		} else {

			$data["cp_page_title"] = $form->title;
			$data["form"] = $form;

			$this->base_url = ee('CP/URL')->make('addons/settings/formgrab/index/'.$form_id);

			$this->generateSidebar( $form_id );

			$total_rows = $formService->countAll();

			$statuses = ee('CP/Filter')->make('status', 'status', array('new'=>'new', 'read'=>'read', 'archived'=>'archived'));
			$statuses->setPlaceholder(lang('all'));
			$statuses->disableCustomValue();

			$filters = ee('CP/Filter')
				->add('Date', 'created_at')
				->add($statuses);
				// ->add('Keyword')
				//->add('Perpage', $total_rows, 'show_all_spam');

			$data['filters'] = $filters->render( $this->base_url );

			$filter_values = $filters->values();
			$filter_fields = array();
			$this->base_url->addQueryStringVariables($filter_values);

			if ( ! empty($filter_values['filter_by_date'])) {
				$filter_fields['created_at'] = $filter_values['filter_by_date'];
			}
			if ( ! empty($filter_values['status'])) {
				$filter_fields['status'] = $filter_values['status'];
			}
			$filter_fields['form_id'] = $form_id;

			$data['table'] = $this->generateTable( $form, $filter_fields );

			$data['pagination'] = $this->generatePaginationLinks( $form, $data["table"]["total_rows"] );

			if( ! $form->save_submission ) {

				if( $form->email_notification ) {
					ee('CP/Alert')->makeInline('formgrab')
				      ->asAttention()
				      ->withTitle(lang('formgrab_save_submission_off'))
				      ->addToBody(
				      		lang('formgrab_save_submission_off_message') .
				      		' <a href="'.ee('CP/URL')->make('addons/settings/formgrab/edit/'.$form->form_id).'">'.lang('update_settings').'</a>'
				      	)
				      ->now();
				} else {
					ee('CP/Alert')->makeInline('formgrab')
				      ->asWarning()
				      ->withTitle(lang('formgrab_save_submission_and_email_off'))
				      ->addToBody(
				      		lang('formgrab_save_submission_and_email_off_message') .
				      		' <a href="'.ee('CP/URL')->make('addons/settings/formgrab/edit/'.$form->form_id).'">'.lang('update_settings').'</a>'
				      	)
				      ->now();
				}
			}

		}

		return array(
			'body' => ee('View')->make('formgrab:index')->render($data),
			'breadcrumb' => array(
				ee('CP/URL')->make('addons/settings/formgrab')->compile() => lang('formgrab_module_name')
			),
			'heading' => lang('formgrab_submissions')
		);

	}

	protected function generateTable( $form, $filters ) {

		$formService = ee('formgrab:Form');
		$subs = ee('Model')->get('formgrab:FormSubmission');

		$subs->filter('site_id', '=', ee()->config->item('site_id'));
		if( !isset($filters['status']) ) {
			$subs->filter('status', '!=', 'archived');
		}

		if ( ! empty($filters)) {
			foreach ($filters as $key => $filter) {
				if ( ! empty($filter) ) {
					if ($key == 'created_at') {
						if (is_array($filter)) {
							$subs->filter('created_at', '>=', $filter[0]);
							$subs->filter('created_at', '<', $filter[1]);
						} else {
							$subs->filter('created_at', '>=', ee()->localize->now - $filter);
						}
					} else {
						$subs->filter($key, $filter);
					}
				}
			}
		}

		$subs = $subs->all();

		// ee()->load->library('relative_date');

		// Create table
		$table = ee('CP/Table',
			array(
				'sort_col' => 'created_at',
				'sort_dir' => 'desc',
				'autosort' => TRUE,
				'limit' => 10,
				// 'autosearch' => TRUE
			)
		);

		// Define columns
		$table->setColumns(
		  array(
			array(
				'label' => 'created_at',
				'sort' => true
			),
			array(
				'label' => 'data',
				'sort' => false,
				'encode' => false
			),
			array(
				'label' => 'status',
				'sort' => true,
				'encode' => false,
			),
			'view' => array(
			  'type'  => Table::COL_TOOLBAR
			),
			array(
			  'type'  => Table::COL_CHECKBOX
			)
		  )
		);

		// Set no results text
		$table->setNoResultsText('formgrab_no_submissions');

		// Build table data array
		$table_data = array();
		foreach ($subs as $sub) {

			$edit_url = ee('CP/URL', 'addons/settings/formgrab/submission_edit/'.$sub->submission_id);

			$dataText = $formService->prettyPrintData( $sub->data, true );
			$status = "open";
			if( $sub->status == "read" ) {
				$status="info";
			}
			if( $sub->status == "archived" ) {
				$status="disable";
			}

			// $relative_date = ee()->relative_date->create($sub->created_at);
			// $units = array('years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds');
			// $relative_date->calculate($units);
			// $depth = isset($parameters['depth']) ? $parameters['depth'] : 1;
			// $dt = $relative_date->render($depth);

			$table_data[] = array(
				array(
					'content' => ee()->localize->human_time($sub->created_at),
				),
				array(
					'content' => ''.$dataText.'',
				),
				array(
					'content' => '<span class="st-'.$status.'">'.$sub->status.'</span>',
				),
				array('toolbar_items' => array(
					'view' => array(
						'href' => $edit_url,
						// 'class' => 'm-link',
						// 'rel' => 'submission-preview',
						// 'data-submission-id' => $sub->submission_id,
						'title' => lang('edit')
					),
				)),
				array(
					'name' => 'submissions[]',
					'value' => $sub->submission_id,
					'data'  => array(
						'confirm' => lang('formgrab_submission') . ': <b>' . htmlentities($sub->submission_id, ENT_QUOTES) . '</b>'
					)
				)
			);
		}
		$table->setData($table_data);

		// $modal_vars = array(
		//   'name' => 'submission-preview',
		//   'contents' => '<p>Hello, world!</p>'
		// );
		// $modal_html = ee('View')->make('formgrab:modal')->render($modal_vars);
		// ee('CP/Modal')->addModal('submission-preview', $modal_html);

		// ee()->javascript->output("
		// $('a.m-link').click(function (e) {
		// 	var modalIs = $('.' + $(this).attr('rel'));
		// 	var subId = $('.' + $(this).data('submission-id'));
		// 	$('#submission-preview', modalIs)
		// 	  .html('<p>Blah ' + subId + '</p>');
		// 	e.preventDefault();
		//   })
		// ");

		return $table->viewData(ee('CP/URL', 'addons/settings/formgrab/index/'.$form->form_id));
	}

	protected function generateSidebar( $active = null )
	{
		$sidebar = ee('CP/Sidebar')->make();

		ee()->javascript->set_global(
			'sets.importUrl',
			ee('CP/URL', 'channels/sets')->compile()
		);
		ee()->cp->add_js_script(array(
			'file' => array('cp/channel/menu','cp/confirm_remove'),
		));

		$forms = $sidebar->addHeader(lang('formgrab_forms'));
		  //->withButton(lang('new'), ee('CP/URL', 'addons/settings/formgrab/add'));

		$formService = ee('formgrab:Form');

		if( count( $formService->all() ) ) {

			$list = $forms->addFolderList('form-list')
				->withRemoveUrl( ee('CP/URL', 'addons/settings/formgrab/delete/') )
				->withRemovalKey('content_id')
				->canReorder();

// ee()->javascript->output("EE.cp.folderList.onSort('form-group', function(list) {
//   // Create an array of form names
//   var template_groups = $.map($('> li', list), function(list_item) {
//     return $(list_item).data('group_name');
//   });

//   $.ajax({
//     url: EE.templage_groups_reorder_url,
//     data: { 'groups': template_groups },
//     type: 'POST',
//     dataType: 'json'
//   });
// });");

			foreach( $formService->all() as $f ) {

				$subsCount = $formService->getNewsSubmissionByFormId($f->form_id);

				$item = $list->addItem(
						$f->title . ( $subsCount ? ' (' . $subsCount . ')' : '' ),
						ee('CP/URL', 'addons/settings/formgrab/index/' . $f->form_id)
					)->withEditUrl( ee('CP/URL', 'addons/settings/formgrab/edit/' . $f->form_id) );

				$item->identifiedBy($f->form_id);

				$item->withRemoveConfirmation(lang('formgrab_form') . ': <b>' . $f->title . '</b>');

				if( $active == $f->form_id ) {
					$item->isActive();
				}

			}
		} else {

			$list = $forms->addBasicList();
			$item = $list->addItem(lang('formgrab_no_forms'));
		}

		return $sidebar;
	}

	protected function generatePaginationLinks( $form, $total_rows ) {

		$paginationUrl = $this->base_url
			->setQueryStringVariable('limit', 10 )
			->setQueryStringVariable('order', 'created_at')
			->setQueryStringVariable('sort', 'desc');

		return ee('CP/Pagination', $total_rows)
			->perPage( ee()->input->get('limit') ? ee()->input->get('limit') : 10 )
			->currentPage( ee()->input->get('page') ? ee()->input->get('page') : 1 )
			->render($paginationUrl);
	}

	public function delete() {
		$form_id = ee()->input->post('content_id');
		$formService = ee('formgrab:Form');
		$formService->deleteById($form_id);
		ee()->functions->redirect(ee('CP/URL')->make('addons/settings/formgrab'));
	}

	public function edit($form_id = null) {

		$formService = ee('formgrab:Form');
		$form = $formService->getById( $form_id );

		$vars = array();

		// If post variables exist then assume form has been sent and so validate
		if( isset( $_POST['name'] ) ) {

			$rules = array(
			  'name' => 'required',
			  'title' => 'required'
			);

			$result = ee('Validation')->make($rules)->validate($_POST);

			if( $result->isValid()) {

				// If it validates, then save results and display success alert

				$f = $formService->getById( $form_id );

				$f->title = ee()->input->post('title');
				$f->name = ee()->input->post('name');
				$f->return_url = ee()->input->post('return_url');
				$f->save_submission = ee()->input->post('save_submission');
				$f->email_notification = ee()->input->post('email_notification');
				$f->email_to = ee()->input->post('email_to');
				$f->email_from_email = ee()->input->post('email_from_email');
				$f->email_from_name = ee()->input->post('email_from_name');
				$f->email_subject = ee()->input->post('email_subject');
				$f->email_body = ee()->input->post('email_body');

				$f->save();

				$alert = ee('CP/Alert')->makeInline('shared-form')
					->asSuccess()
					->withTitle(lang('success'))
					->addToBody('Settings saved')
					->now();

			} else {

				// If it does not validate, display error alert and add errors to vars

				$vars['errors'] = $result;
				ee('CP/Alert')->makeInline('shared-form')
					->asIssue()
					->withTitle(lang('settings_save_error'))
					->addToBody(lang('settings_save_error_desc'))
					->now();

			}
		}

		// Build form
		$vars['sections'] = array(
			array(
				array(
					'title' => 'Name',
					'desc' => 'A short name used to identify the form',
					'fields' => array(
						'name' => array(
							'type' => 'text',
							'value' => ee()->input->post('name') !== FALSE ? ee()->input->post('name') : $form->name,
							'required' => TRUE
						)
					)
				),
				array(
					'title' => 'Title',
					'desc' => 'The name used to describe the form',
					'fields' => array(
						'title' => array(
							'type' => 'text',
							'value' => ee()->input->post('title') !== FALSE ? ee()->input->post('title') : $form->title,
							'required' => TRUE
						)
					)
				),
				array(
					'title' => 'Return URL',
					'desc' => 'The url or path that the user is redirected to after submission',
					'fields' => array(
						'return_url' => array(
							'type' => 'text',
							'value' => ee()->input->post('return_url') !== FALSE ? ee()->input->post('return_url') : $form->return_url
						)
					)
				),
				array(
					'title' => 'Save submissions to database?',
					'desc' => 'If this is turned on, submissions will be saved to the database. If you do not want to retain the submissions (eg, if you are sent emails) then you can turn this off',
					'fields' => array(
						'save_submission' => array(
							'type' => 'yes_no',
							'choices' => array(
								'y' => 'Yes',
								'n' => 'No',
							),
							'value' => ee()->input->post('save_submission') !== FALSE ? ee()->input->post('save_submission') : $form->save_submission
						)
					)
				),
				array(
					'title' => 'Email notification?',
					'desc' => 'Send an email when a form is submitted',
					'fields' => array(
						'email_notification' => array(
							'type' => 'yes_no',
							'choices' => array(
								'y' => 'Yes',
								'n' => 'No',
							),
							'group_toggle' => array(
								'y' => 'email_options',
							),
							'value' => ee()->input->post('email_notification') !== FALSE ? ee()->input->post('email_notification') : $form->email_notification
						)
					)
				),
			),
			'Email Notification Options' => array(
				'group' => 'email_options',
					'settings' => array(
						array(
							'title' => 'Send email to',
							'desc' => 'A comma separated list of recipients',
							'fields' => array(
								'email_to' => array(
									'type' => 'text',
									'value' => ee()->input->post('email_to') !== FALSE ? ee()->input->post('email_to') : $form->email_to
								)
							),
						),
						array(
							'title' => 'Email subject line',
							'fields' => array(
								'email_subject' => array(
									'type' => 'text',
									'value' => ee()->input->post('email_subject') !== FALSE ? ee()->input->post('email_subject') : $form->email_subject
								)
							),
						),
						array(
							'title' => 'Email from address',
							'fields' => array(
								'email_from_email' => array(
									'type' => 'text',
									'value' => ee()->input->post('email_from_email') !== FALSE ? ee()->input->post('email_from_email') : $form->email_from_email
								)
							),
						),
						array(
							'title' => 'Email from name',
							'fields' => array(
								'email_from_name' => array(
									'type' => 'text',
									'value' => ee()->input->post('email_from_name') !== FALSE ? ee()->input->post('email_from_name') : $form->email_from_name
								)
							),
						),
						array(
							'title' => 'Email body',
							'desc' => 'You can use variables that will be replaced in the sent message. Variables inlude: {form_title}, {submission_date} and any values from form inputs eg, {address1}',
							'fields' => array(
								'email_body' => array(
									'type' => 'textarea',
									'value' => ee()->input->post('email_body') !== FALSE ? ee()->input->post('email_body') : $form->email_body
								)
							),
						),
					),
			)
		);

		$vars += array(
			'base_url' => ee('CP/URL', 'addons/settings/formgrab/edit/'.$form_id),
			'cp_page_title' => lang('formgrab_edit_form'),
			'save_btn_text' => sprintf(lang('btn_save'), lang('formgrab_form')),
			'save_btn_text_working' => 'btn_saving'
		);

		$this->generateSidebar( $form_id );

		return array(
			'body' => ee('View')->make('ee:_shared/form')->render($vars),
			'breadcrumb' => array(
				ee('CP/URL')->make('addons/settings/formgrab')->compile() => lang('formgrab_module_name')
			),
			'heading' => lang('formgrab_edit_form')
		);

	}

	public function submission_remove() {
		foreach( ee()->input->post("submissions") as $sub_id ) {
			$s = ee('Model')->get('formgrab:FormSubmission')->filter('submission_id', $sub_id)->first();
			if( $s ) {
				$s->delete();
			}
		}
		ee()->functions->redirect(ee('CP/URL')->make('addons/settings/formgrab/index/' . ee()->input->post('form_id') ));
	}

	public function submission_edit( $sub_id ) {

		$formService = ee('formgrab:Form');
		$sub = $formService->getSubmissionById( $sub_id );

		$sub->status = 'read';
		$sub->save();

		$data = array();
		$data["form_id"] = $sub->Form->form_id;
		$data["items"] = array();

		$data["items"]["formgrab_form_details"] = "<b>" . lang('formgrab_form_title') . '</b>: ' . $sub->Form->title;
		$data["items"]["formgrab_form_details"] .= "<br/><b>" . lang('formgrab_form_name') . '</b>: ' . $sub->Form->name;

		$data["items"]["formgrab_created_at"] = ee()->localize->human_time( $sub->created_at );
		if( $sub->Member ) {
			$data["items"]["formgrab_member"] = "<b>" . lang('formgrab_member_id') . '</b>: ' . $sub->member_id;
			$data["items"]["formgrab_member"] .= "<br/><b>" . lang('formgrab_member_email') . '</b>: ' . $sub->Member->email;
			$data["items"]["formgrab_member"] .= "<br/><b>" . lang('formgrab_member_screen_name') . '</b>: ' . $sub->Member->screen_name;
			$data["items"]["formgrab_member"] .= "<br/><b>" . lang('formgrab_member_username') . '</b>: ' . $sub->Member->username;
		}
		$data["items"]["formgrab_url"] = $sub->url;
		$data["items"]["formgrab_ip_address"] = $sub->ip_address;
		$data["items"]["formgrab_data"] = $formService->prettyPrintData($sub->data);

		$this->generateSidebar( $sub->Form->form_id );

		return array(
			'body' => ee('View')->make('formgrab:submission')->render($data),
			'breadcrumb' => array(
				ee('CP/URL')->make('addons/settings/formgrab')->compile() => lang('formgrab_module_name')
			),
			'heading' => lang('formgrab_submission')
		);
	}

	public function export( $form_id ) {
		$formService = ee('formgrab:Form');
		$form = $formService->getById( $form_id );
		$sub = $formService->getSubmissionByFormId( $form_id );
		$csv = ee('CSV');
		foreach( $sub as $s ) {
			$row = array();
			$fields = $formService->getSubmissionFieldArray( $s );
			foreach( $fields as $key => $field ) {
				if( !is_array($field) ) {
					$row[$key] = $field;
				} else {
					foreach( $field[0] as $key2 => $field2 ) {
						$row[$key2] = $field2;
					}
				}
			}
			$csv->addRow( $row );
		}
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$form->name.'.csv"');
		echo (string) $csv;
		exit;
	}

}
/* End of file mcp.formgrab.php */
/* Location: /system/expressionengine/third_party/formgrab/mcp.formgrab.php */