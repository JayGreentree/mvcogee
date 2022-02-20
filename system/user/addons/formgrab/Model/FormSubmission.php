<?php

namespace Brandnewbox\Formgrab\Model;

use EllisLab\ExpressionEngine\Service\Model\Model;

class FormSubmission extends Model {

	protected static $_primary_key = 'submission_id';
	protected static $_table_name = 'formgrab_submissions';

	protected $submission_id;
	protected $form_id;
	protected $site_id;
	protected $member_id;

	protected $url;
	protected $data;
	protected $status;
	protected $ip_address;

	protected $created_at;
	protected $updated_at;

	protected static $_relationships = array(
	  'Form' => array(
	    'model' => 'Form',
	    'type' => 'belongsTo'
	  ),
	  'Member' => array(
			'type' => 'belongsTo',
			'model' => 'ee:Member'
		)
	);

	protected static $_typed_columns = array(
	  'submission_id' => 'int',
	  'form_id' => 'int',
	  'site_id' => 'int',
	  'member_id' => 'int',
	  // 'created_at' => 'timestamp',
	  // 'updated_at' => 'timestamp'
	);


}

// EOF