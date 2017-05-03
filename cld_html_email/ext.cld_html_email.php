<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * @author		James Smith http://www.jamessmith.co.uk
 * @link		https://cld.agency
 */

class Cld_html_email_ext {

	public $settings 		= array();
	public $description		= 'Sets all outgoing EE email to use the format specified in the control panel config settings';
	public $docs_url		= '';
	public $name			= 'CLD HTML Email';
	public $settings_exist	= 'n';
	public $version;

	private $hooks = array(
		'email_send',
	);


// ----------------------------------------------------------------------

	public function __construct($settings = '')
	{
		include PATH_THIRD.'cld_html_email/config.php';
		$this->version = $config['version'];
		$this->settings = $config['default_settings'];
		$this->class_name = ucfirst(get_class($this));
	}

	// --------------------------------------------------------------------

	public function activate_extension()
	{
		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}
	}

	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$data = array();
		$data['version'] = $this->version;

		// Update records using data array
		ee()->db->where('class', $this->class_name);
		ee()->db->update('extensions', $data);
	}

	public function disable_extension()
	{
		// Delete records
		ee()->db->where('class', $this->class_name);
		ee()->db->delete('extensions');
	}

	private function _add_hook($hook)
	{
		ee()->db->insert('extensions', array(
			'class'    => $this->class_name,
			'method'   => $hook,
			'hook'     => $hook,
			'settings' => serialize($this->settings),
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	// --------------------------------------------------------------------
	// --------------------------------------------------------------------
	// --------------------------------------------------------------------

	/**
	 * Reset all outgoing email to use HTML format
	 *
	 * @return no return, just amend $data directly via reference
	 */
        public function email_send($data)
        {
          $data['headers']['mailtype'] = ee()->config->item('mail_format');
          $data['header_str'] = str_replace("Content-Type: text/plain; charset=utf-8", "Content-type: text/html; charset=utf-8", $data['header_str']);

          //add it if it's not there
          if (strpos($data['header_str'], "Content-type: text/html; charset=utf-8") === false
              && strpos($data['header_str'], "Content-Type: multipart/alternative") === false)
          {
              $data['header_str'] .= "Content-type: text/html; charset=utf-8";
          }
        }
}
// EOF
