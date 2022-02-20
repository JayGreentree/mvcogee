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
 * FormGrab Module Install/Update File
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Module
 * @author     Andrew Weaver
 * @link       http://brandnewbox.co.uk/
 */
class Formgrab_upd
{
    public $version = '1.0.4';

    /**
     * Installation Method
     *
     * @return  boolean
     */
    public function install()
    {
        ee()->db->insert('modules', array(
            'module_name'        => 'Formgrab',
            'module_version'     => $this->version,
            'has_cp_backend'     => 'y',
            'has_publish_fields' => 'n',
        ));

        // Action Install
        ee()->db->insert('actions', array(
            'class'  => 'Formgrab',
            'method' => 'submit_form'
        ));

        // Custom Table Install
        ee()->load->dbforge();

        $fields = array(
            // Keys
            'form_id'    => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'site_id'    => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),

            // Text
            'name'       => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),
            'title'      => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),

            // Options
            'save_submission' => array('type' => 'CHAR', 'constraint' => '1', 'default' => 'y'),
            'status'          => array('type' => 'VARCHAR', 'constraint' => '64', 'default' => 'open'),
            'return_url'      => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),

            'email_notification' => array('type' => 'CHAR', 'constraint' => '1', 'default' => 'n'),
            'email_to'           => array('type' => 'VARCHAR', 'constraint' => '512', 'default' => ''),
            'email_subject'      => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),
            'email_body'         => array('type' => 'TEXT'),
            'email_from_email'   => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),
            'email_from_name'    => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),

            'view_order'      => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),

            // Dates
            'created_at' => array('type' => 'INT', 'constraint' => '10', 'null' => FALSE),
            'updated_at' => array('type' => 'INT', 'constraint' => '10', 'null' => FALSE),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('form_id', TRUE);
        ee()->dbforge->add_key('site_id');
        ee()->dbforge->create_table('formgrab_forms', TRUE);

        $fields = array(
            // Keys
            'submission_id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'site_id'       => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'form_id'       => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'member_id'     => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'ip_address'    => array('type' => 'VARCHAR' , 'constraint' => '45'),

            // Options
            'status'     => array('type' => 'VARCHAR', 'constraint' => '64', 'default' => 'new'),

            // Text
            'url'        => array('type' => 'VARCHAR', 'constraint' => '255', 'default' => ''),
            'data'       => array('type' => 'TEXT'),

            // Dates
            'created_at' => array('type' => 'INT', 'constraint' => '10', 'null' => FALSE),
            'updated_at' => array('type' => 'INT', 'constraint' => '10', 'null' => FALSE),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('submission_id', TRUE);
        ee()->dbforge->add_key('site_id');
        ee()->dbforge->add_key('form_id');
        ee()->dbforge->create_table('formgrab_submissions', TRUE);

        return TRUE;
    }

    /**
     * Uninstall
     *
     * @return  boolean
     */
    public function uninstall()
    {
        ee()->db->where('class', 'Formgrab')->delete('actions');

        $mod_id = ee()->db->select('module_id')->get_where('modules', array('module_name' => 'Formgrab'))->row('module_id');
        ee()->db->where('module_id', $mod_id)->delete('module_member_groups');
        ee()->db->where('module_name', 'Formgrab')->delete('modules');

        // Custom Tables Uninstall
        ee()->load->dbforge();
        ee()->dbforge->drop_table('formgrab_forms');
        ee()->dbforge->drop_table('formgrab_submissions');

        return TRUE;
    }

    /**
     * Module Updater
     *
     * @return  boolean
     */
    public function update($current = '')
    {
        if (version_compare($current, '1.0.1', '<')) {

           ee()->load->dbforge();
           $fields = array(
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 45
            )
        );
           ee()->dbforge->add_column('formgrab_submissions', $fields, 'member_id');
       }

        return TRUE;
    }
}

/* End of file upd.formgrab.php */
/* Location: /system/expressionengine/third_party/formgrab/upd.formgrab.php */