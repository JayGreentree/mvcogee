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
 * FormGrab Module Front End File
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Module
 * @author     Andrew Weaver
 * @link       http://brandnewbox.co.uk/
 */
class Formgrab
{
	public $return_data;

	/**
	* Constructor
	*/
	public function __construct()
	{
	}

	/**
	 * Template tag to build form
	 */
	public function form() {

		// Find the name
		$name = ee()->TMPL->fetch_param('name');
		if( $name === FALSE ) {
			return "";
		}

		// Create encrypted list of parameters
		$parameters = array();
		foreach( ee()->TMPL->tagparams as $f => $v ) {
			switch( $f ) {
				case 'title':
				case 'name':
				case 'required': {
					$parameters[ 'formgrab_' . $f ] = $v;
					break;
				}
				default: {
					$parameters[ $f ] = $v;
				}
			}
		}
		$plist = ee('Encrypt')->encode( serialize( $parameters ) );

		// Build an array to hold the form's hidden fields
		$hidden_fields = array(
			"ACT" => ee()->functions->fetch_action_id( 'Formgrab', 'submit_form' ),
			"parameters" => $plist
		);

		// Build an array with the form data
		$form_data = array(
			"id" => ee()->TMPL->form_id,
	        "class" => ee()->TMPL->form_class,
			"hidden_fields" => $hidden_fields
		);

		// Fetch contents of the tag pair, ie, the form contents
		$tagdata = ee()->TMPL->tagdata;

		if (ee()->extensions->active_hook('formgrab_form_tagdata') === TRUE) {
			$tagdata = ee()->extensions->call('formgrab_form_tagdata', $tagdata);
		}

		$form = ee()->functions->form_declaration($form_data);

		if( ee()->TMPL->fetch_param('attributes') ) {
			$form = preg_replace('/(<form\b[^><]*)>/i', '$1 '.ee()->TMPL->fetch_param('attributes').'>', $form);
		}

		$form .= $tagdata .
			"</form>";

		return $form;
	}

	public function display() {
		$submission_id = ee()->TMPL->fetch_param('submission_id');

		$formService = ee('formgrab:Form');
		$sub = $formService->getSubmissionById( $submission_id );
		if( is_null( $sub ) ) {
			return ee()->TMPL->no_results;
		}

		$fields = $formService->getSubmissionFieldArray( $sub );

		$tagdata = ee()->TMPL->tagdata;

		return ee()->TMPL->parse_variables( $tagdata, array( $fields ) );
	}

	/*
		ACTIONS
	*/

	/**
	 *  Action when form is submitted
	 */
	public function submit_form() {

		// Check for parameters field
		if( ee()->input->post('parameters') == FALSE ) {
			ee()->lang->loadfile('formgrab');
			$errors[] = lang('formgrab_submitted_error');
			ee()->output->show_user_error('general', $errors);
		}

		// Get parameters
		$plist = ee()->input->post('parameters');
		$parameters = @unserialize( ee('Encrypt')->decode( $plist ) );

		// Check if form exists and is 'valid'

		if( !isset( $parameters['formgrab_name']) ) {
			ee()->lang->loadfile('formgrab');
			$errors[] = lang('formgrab_submitted_error');
			ee()->output->show_user_error('general', $errors);
		}

		// If new form, then create it
		$formService = ee('formgrab:Form');
		$form = $formService->findOrCreate( $parameters );

		// Process form
		// Save data to submissions table if allowed
		// Do other stuff if required (eg, email)
		$status = $formService->saveSubmission( $form, $parameters );

		// Handle response

		if(AJAX_REQUEST) {
			if( $status === true ) {
				$return = json_encode(array(
	                'status'  => 'ok'
	            ));
			} else {
				$return = json_encode(array(
	                'status'  => 'error',
	                'errors' => $status
	            ));
	            // http_response_code(400);
			}
            header('Content-Type: application/javascript');
            exit( $return );
		}

		if( $status !== true ) {
			$errors = array();
			foreach( $status as $field => $error ) {
				foreach( $error as $e ) {
					$errors[] = $field . ': ' . $e;
				}
			}
			return ee()->output->show_user_error('general', $errors);
			exit;
		}

		if( $form->return_url != '' ) {
			 ee()->functions->redirect( $form->return_url );
		}

		ee()->lang->loadfile('formgrab');

		$data = array(
			'title' => lang('formgrab_submitted_title'),
			'heading' => lang('formgrab_submitted_heading'),
			'content' => lang('formgrab_submitted_content'),
			'link' => array(ee()->functions->fetch_site_index(), lang('formgrab_submitted_link'))
		);
		ee()->output->show_message($data); 

		// Redirect to url, send ajax response
		// $return = ee()->functions->create_url('site/form/thanks');
		// ee()->functions->redirect($return);

		// print_r( $_REQUEST ); exit;
	}

}

/* End of file mod.formgrab.php */
/* Location: /system/expressionengine/third_party/formgrab/mod.formgrab.php */
