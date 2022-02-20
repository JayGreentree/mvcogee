<?php

return array(
	'author'         => 'Andrew Weaver',
	'author_url'     => 'http://brandnewbox.co.uk/',
	'name'           => 'FormGrab',
	'description'    => 'Add simple form end-points',
	'version'        => '1.0.4',
	'namespace'      => 'Brandnewbox\Formgrab',
	'settings_exist' => TRUE,
	'models' => array(
		'Form' => 'Model\Form',
		'FormSubmission' => 'Model\FormSubmission',
	),
	'services' => array(
		'Form' => 'Service\Form',
	)
);