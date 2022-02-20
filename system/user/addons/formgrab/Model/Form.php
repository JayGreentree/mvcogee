<?php

namespace Brandnewbox\Formgrab\Model;

use EllisLab\ExpressionEngine\Service\Model\Model;

class Form extends Model {

	protected static $_primary_key = 'form_id';
	protected static $_table_name = 'formgrab_forms';

	protected $form_id;
	protected $site_id;
	protected $title;
	protected $name;
	protected $save_submission;
	protected $status;
	protected $view_order;
	protected $return_url;

	protected $email_notification;
	protected $email_to;
	protected $email_from_email;
	protected $email_from_name;
	protected $email_subject;
	protected $email_body;

	protected $created_at;
	protected $updated_at;

	protected static $_relationships = array(
	  'Submissions' => array(
	    'model' => 'FormSubmission',
	    'type' => 'HasMany'
	  )
	);

	protected static $_typed_columns = array(
	  'form_id' => 'int',
	  'site_id' => 'int',
	  'save_submission' => 'yesNo',
	  'email_notification' => 'yesNo',
	  'view_order' => 'int',
	  'created_at' => 'timestamp',
	  'updated_at' => 'timestamp'
	);


}

// EOF