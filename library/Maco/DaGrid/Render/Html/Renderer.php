<?php

class Maco_DaGrid_Render_Html_Renderer
{
	/**
	 * The grid owner of this renderer
	 *
	 * @var Maco_Da_Grid_Grid
	 */
	protected $_grid;

	/**
	 * Path to the template directory
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * The view that this grid will be injected into.
	 * Useful to get the used filters and more...
	 *
	 * @param Zend_View
	 * @return void
	 */
	protected $_view;

	/**
	 * Default Template
	 *
	 * @var string
	 */
	protected $_defaultTpl = 'default.phtml';

	/**
	 * Grid URI
	 *
	 * @var string
	 */
	protected $_uri;

    /**
     * Grid PathInfo
     *
     * @var string
     */
    protected $_pathInfo;

	/**
	 * Raw URI
	 *
	 * @var string
	 */
	protected $_rawUri;

	/**
	 * Css classes for the html element
	 *
	 * @var array
	 */
	protected $_cssClasses = array();

	/**
	 * Id for the html element
	 *
	 * @var mixed
	 */
	protected $_id = '';

	/**
	 * The base url
	 *
	 * @var string
	 */
	protected $_baseUrl;

	/**
	 * Strict variables flag; when on, undefined variables accessed in the view
	 * scripts will trigger notices
	 * @var boolean
	 */
	protected $_strictVars = false;

	public function __construct($path_to_template = NULL)
	{
		if($path_to_template !== NULL)
		{
			$this->_path = $path_to_template;
		}
		else
		{
			$this->_path = LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Maco'
			. DIRECTORY_SEPARATOR . 'DaGrid' . DIRECTORY_SEPARATOR . 'Render'
			. DIRECTORY_SEPARATOR . 'Html' . DIRECTORY_SEPARATOR . 'scripts'
			. DIRECTORY_SEPARATOR;
		}

		$this->_baseUrl = $this->_getBaseUrl();
	}

	/**
	 * REturn the base url
	 *
	 * @return stringg
	 */
	protected function _getBaseUrl()
	{
		return Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
	}

	/**
	 * Sets the view for this grid
	 *
	 * @param Zend_View $view
	 */
	public function setView(&$view)
	{
		$this->_view = $view;
	}

	public function addClasses($class)
	{
		if(is_string($class))
		{
			$class = array($class);
		}

		$this->_cssClasses = array_merge($this->_cssClasses, $class);
	}

	public function setId($id)
	{
		if($this->_id == '')
		{
			$this->_id = $id;
		}
	}

	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Enable or disable strict vars
	 *
	 * If strict variables are enabled, {@link __get()} will raise a notice
	 * when a variable is not defined.
	 *
	 * Use in conjunction with {@link Zend_View_Helper_DeclareVars the declareVars() helper}
	 * to enforce strict variable handling in your view scripts.
	 *
	 * @param  boolean $flag
	 * @return Zend_View_Abstract
	 */
	public function strictVars($flag = true)
	{
		$this->_strictVars = ($flag) ? true : false;

		return $this;
	}

	/**
	 * Prevent E_NOTICE for nonexistent values
	 *
	 * If {@link strictVars()} is on, raises a notice.
	 *
	 * @param  string $key
	 * @return null
	 */
	public function __get($key)
	{
		if ($this->_strictVars) {
			trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
		}

		return null;
	}

	public function render($template = NULL)
	{
		if($template === NULL)
		{
			$template = $this->_defaultTpl;
		}

		$this->_file = $this->_script($template);
		unset($template); // remove $name from local scope

		ob_start();

		$this->_run($this->_file);

		echo $this->_filter(ob_get_clean()); // filter output
		//    ob_end_clean();
	}

	/**
	 * Finds a view script from the available directories.
	 *
	 * @param $name string The base name of the script.
	 * @return void
	 */
	protected function _script($name)
	{
		if (is_readable($this->_path . $name)) {
			return $this->_path . $name;
		}

		require_once 'Zend/View/Exception.php';
		$message = "script '$name' not found in path ("
		. $this->_path
		. ")";
		$e = new Zend_View_Exception($message);
		if($this->_view !== NULL)
		{
			$e->setView($this->_view);
		}
		throw $e;
	}

	protected function _run()
	{
		/*
		 if ($this->_useViewStream && $this->useStreamWrapper())
		 {
		 include 'zend.view://' . func_get_arg(0);
		 }
		 else
		 */
		{
			include func_get_arg(0);
		}
	}

	/**
	 * Applies the filter callback to a buffer.
	 *
	 * @param string $buffer The buffer contents.
	 * @return string The filtered buffer.
	 */
	private function _filter($buffer)
	{
		if($this->_view !== NULL)
		{
			$filters = $this->_view->getFilters();
			// loop through each filter class
			foreach ($filters as $name) {
				// load and apply the filter class
				$filter = $this->_view->getFilter($name);
				$buffer = call_user_func(array($filter, 'filter'), $buffer);
			}
		}
		// done!
		return $buffer;
	}

    /**
     * Sets the path info for this grid
     *
     * @param string $pathInfo
     * @return void
     */
    public function setPathInfo(&$pathInfo)
    {
        $this->_pathInfo = $pathInfo;
    }

	/**
	 * Sets the uri for this grid
	 *
	 * @param string $uri
	 * @return void
	 */
	public function setUri(&$uri)
	{
		$this->_uri = $uri;
	}

	/**
	 * Sets the raw uri for this grid
	 *
	 * @param string $uri
	 * @return void
	 */
	public function setRawUri(&$rawUri)
	{
		$this->_rawUri = $rawUri;
	}

	/**
	 * REturns the uri for this grid
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->_uri;
	}

	/**
	 * REturns the raw uri for this grid
	 *
	 * @return string
	 */
	public function getRawUri()
	{
		return $this->_rawUri;
	}

	/**
	 * Sets the grid owner of this renderer
	 *
	 * @param Maco_DaGrid_Grid
	 */
	public function setGrid(Maco_DaGrid_Grid &$grid)
	{
		$this->_grid = $grid;
	}
}
