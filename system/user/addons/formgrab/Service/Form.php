<?php
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

namespace Brandnewbox\Formgrab\Service;

/**
 * OEmbed Module Embed Service File
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Module
 * @author     Andrew Weaver
 * @link       http://brandnewbox.co.uk/
 */
class Form
{

	/**
	 * Returns a collection of all forms
	 */
	public function all() {
		return ee('Model')->get('formgrab:Form')->filter('site_id', '=', ee()->config->item('site_id'))->all();
	}

	/**
	 * Returns a collection of all forms
	 */
	public function countAll() {
		return ee('Model')->get('formgrab:Form')->filter('site_id', '=', ee()->config->item('site_id'))->count();
	}


	/**
	 * Returns a single form by form_id
	 */
	public function getById( $form_id ) {
		return ee('Model')->get('formgrab:Form')->filter('form_id', $form_id)->first();
	}

	/**
	 * Deletes a form by form_id
	 */
	public function deleteById( $form_id ) {
		return ee('Model')->get('formgrab:Form')->filter('form_id', $form_id)->delete();
	}


	public function findOrCreate( $parameters ) {

		// Find form from $POSTed formgrab_name
		$form = ee('Model')->get('formgrab:Form')->filter('site_id', '=', ee()->config->item('site_id'))->filter('name', $parameters['formgrab_name'])->first();

// print_r( $form ); exit;

		// If it does not exist, then make a new form
		if( is_null( $form ) ) {

			if( ee()->config->item('formgrab_prevent_new_forms') == 'y') {
				$error = array();
				ee()->lang->loadfile('formgrab');
                $error[] = lang('formgrab_no_new_forms');
                return ee()->output->show_user_error('general', $error);
			}

			$data = array(
				'site_id' => ee()->config->item('site_id'),
				'name' => $parameters['formgrab_name'],
				'view_order' => $this->getNextViewOrder(),
				'created_at' => ee()->localize->now,
				'updated_at' => ee()->localize->now,
			);

			if( $parameters['formgrab_title'] ) {
				$data['title'] = $parameters['formgrab_title'];
			} else {
				$data['title'] = $parameters['formgrab_name'];
			}

			$form = ee('Model')->make('formgrab:Form', $data)->save();
			$form = ee('Model')->get('formgrab:Form')->filter('name', $parameters['formgrab_name'])->first();
		}

		return $form;
	}

	/**
	 * Returns a single submission by submission_id
	 */
	public function getSubmissionById( $sub_id ) {
		return ee('Model')->get('formgrab:FormSubmission')->filter('submission_id', $sub_id)->first();
	}

	/**
	 * Returns a single submission by submission_id
	 */
	public function getSubmissionByFormId( $form_id ) {
		return ee('Model')->get('formgrab:FormSubmission')->filter('form_id', $form_id)->all();
	}

	/**
	 * Returns count of new submission by submission_id
	 */
	public function getNewsSubmissionByFormId( $form_id, $status = 'new' ) {
		return ee('Model')->get('formgrab:FormSubmission')->filter('form_id', $form_id)->filter('status', $status)->count();
	}


	public function markSubmissions( $sub_ids, $status="read" ) {
		foreach( $sub_ids as $sub_id ) {
			$s = ee('Model')->get('formgrab:FormSubmission')->filter('submission_id', $sub_id)->first();
			$s->status = $status;
			$s->save();
		}
	}

	/**
	 * Processes the form the submission
	 */
	public function saveSubmission( $form, $parameters ) {

		// Do not save these fields
		$skip_fields = array(
			'ACT',
			'site_id',
			'parameters',
			'csrf_token'
		);

		// Loop over all inputs and clena them up
		$post = array();
		foreach( $_POST as $key => $value ) {
			if( in_array($key, $skip_fields) ) continue;
			$post[ $key ] = ee()->input->post($key, TRUE);
		}

		if( isset($parameters['formgrab_required']) ) {
			$required = explode('|', trim($parameters['formgrab_required']));
			$rules = array();
			foreach( $required as $rule ) {
				$rules[ $rule ] = 'required';
			}
			$result = ee('Validation')->make($rules)->validate($post);
			if( $result->isNotValid() ) {
				$errors = $result->getAllErrors();
				return $errors;
			}
		}

		if (ee()->extensions->active_hook('formgrab_validate') === TRUE) {
			$post = ee()->extensions->call('formgrab_validate', $post, $parameters);
		}

		// Create submission model
		$data = array(
			'form_id' => $form->form_id,
			'site_id' => ee()->config->item('site_id'),
			'member_id' => ee()->session->userdata('member_id'),
			'url' => ee()->session->tracker['1'],
			'data' => json_encode($post),
			'ip_address' => ee()->input->ip_address(),
			'created_at' => ee()->localize->now,
			'updated_at' => ee()->localize->now,
		);

		$sub = ee('Model')->make('formgrab:FormSubmission', $data);

		// $check_for_spam = implode(", ", $post);
		// $is_spam = ee('Spam')->isSpam( $check_for_spam );
		// if ($is_spam) {
		// 	ee('Spam')->moderate('Formgrab', $sub, $check_for_spam, array() );
		// } else {

			if (ee()->extensions->active_hook('formgrab_save_submission') === TRUE) {
				$sub = ee()->extensions->call('formgrab_save_submission', $sub, $post, $parameters);
				if (ee()->extensions->end_script === TRUE) return;
			}

			// Save model to database
			if( $form->save_submission ) {
				$sub->save();
			}

			// Email notification
			if( $form->email_notification ) {

				// Create and send email
				ee()->load->library('email');
				ee()->load->helper('text');

				ee()->email->wordwrap = true;
				ee()->email->mailtype = 'text';

				// Get form data as array
				$vars = $this->getSubmissionFieldArray( $sub );

				// Use webmaster form default email addresses if none are set
				$from_name = $form->email_from_name != '' ? $form->email_from_name : ee()->config->item('webmaster_name');
				$from_name = ee()->functions->var_swap($from_name, $vars);

				$from_email = $form->email_from_email != '' ? $form->email_from_email : ee()->config->item('webmaster_email');
				$from_email = ee()->functions->var_swap($from_email, $vars);

				if( ee('Validation')->check('email', $from_email) == false ) {
					$from_email = ee()->config->item('webmaster_email');
				}


				$to = $form->email_to != '' ? $form->email_to : ee()->config->item('webmaster_email');
				$to = ee()->functions->var_swap($to, $vars);

				// Validate email address (may be in comma separated list)
				foreach( explode(",", $to ) as $t ) {
					if( ee('Validation')->check('email', trim($t) ) == false ) {
						$to = ee()->config->item('webmaster_email');
					}
				}

				// Swap form data into subject variables
				$subject = $form->email_subject != '' ? $form->email_subject : '{form_title}';
				$subject = ee()->functions->var_swap($subject, $vars);

				// Swap form data into email body variables
				$body = $form->email_body != '' ? $form->email_body : 'You have received a submission from the form: {form_title}';
				$body = ee()->functions->var_swap($body, $vars);

				// Send email
				ee()->email->from( ee()->config->item('webmaster_email'), $from_name );
				ee()->email->reply_to( $from_email, $from_name);
				ee()->email->to( $to );
				ee()->email->subject( $subject );
				ee()->email->message( entities_to_ascii( $body ) );

				// ee()->email->debug = true;
				ee()->email->Send();
			}

		// } // end check for spam

		return true;
	}

	public function getNextViewOrder() {
		ee()->db->select('max(view_order)+1 as view_order');
		ee()->db->from('formgrab_forms');
		ee()->db->where('site_id', ee()->config->item('site_id'));
		$query = ee()->db->get();
		$row = $query->row();
		if( is_null( $row->view_order ) ) {
			return 1;
		}
		return $row->view_order;
	}

	public function prettyPrintData( $jsondata, $shorten = false ) {
		$dataArray = json_decode($jsondata);
		$dataText = '';
		foreach( $dataArray as $key => $value ) {
			if( is_array( $value ) ) {
				$value = array_filter($value, function($v){ return trim($v); });
				$value = implode(", ", $value);
			}
			$value = strip_tags($value);
			if( $shorten ) {
				if ( strlen( $value ) > 64 ) {
					$value = substr( $value, 0, 64 ) . "...";
				}
			} else {
				$value = nl2br( $value );
			}
			$dataText .= '<b>'.ucfirst(str_replace(array("_","-"), array(" "," "), $key)).'</b>: '.$value.'<br/>';
		}
		return $dataText;
	}

	public function getSubmissionFieldArray( $sub ) {

		$fields = array();

		$fields["form_title"] = $sub->Form->title;
		$fields["form_name"] = $sub->Form->name;
		$fields["submission_date"] = ee()->localize->human_time( $sub->created_at );
		$fields["status"] = $sub->status;
		$fields["url"] = $sub->url;
		$fields["ip_address"] = $sub->ip_address;
		$fields["member_id"] = $sub->member_id;
		if( $sub->Member ) {
			$fields["member_email"] = $sub->Member->email;
			$fields["member_username"] = $sub->Member->username;
			$fields["member_screen_name"] = $sub->Member->screen_name;
		}
		$data = json_decode($sub->data);
		foreach( $data as $key => $value ) {
			if( is_array( $value ) ) {
				// remove empty elements
				$value = array_filter($value, function($v){ return trim($v); });
				// concatenate
				$value = implode(", ", $value);
			}
			$fields[ $key ] = $value;
		}

		return $fields;
	}

}

// EOF
