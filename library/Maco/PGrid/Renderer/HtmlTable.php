<?php

class Maco_PGrid_Renderer_HtmlTable implements Maco_PGrid_Renderer_Interface
{
	/**
	 * Options
	 *
	 * @var array
	 */
	protected $_options = array(
        'id' => null,
        'class' => array('fluid'),
        'rowAltClass' => array('odd', 'even'),
        'sortable' => false,
        'sortableFields' => array(),
        'orderBy' => array()
	);

	/**
	 * The fields to display
	 *
	 * @param array
	 */
	protected $_fields = array();

	/**
	 * Data source
	 *
	 * @var mixed
	 */
	protected $_source;

	/**
	 * Actual url
	 *
	 * @var string
	 */
	protected $_url;

	/**
	 * Output html
	 *
	 * @var string
	 */
	protected $_output = "";

	/**
	 * already printed ajax class
	 *
	 * @var bool
	 */
	protected static $_ajaxClassPrinted = false;

	public function buildFilters()
	{}

	public function buildGrid()
	{}

	public function setOptions(&$options)
	{
		$this->_options = array_merge($this->_options, $options);
	}

	protected function _getUrl()
	{
		if(!$this->_url)
		{
			$front = Zend_Controller_Front::getInstance();
			$baseUrl = $front->getBaseUrl();
			$uri = $front->getRequest()->getRequestUri();
			$this->_url = $baseUrl . $uri;
		}
		return $this->_url;
	}

	public function buildTitles()
	{
		if($this->_options['useAjax'])
		{
			$req = Zend_Controller_Front::getInstance()->getRequest();
			$_s = $req->getParam('_s', false);
			$_d = $req->getParam('_d', false);
			if($_s)
			{
				$this->_options['orderBy'] = array();
				$this->_options['orderBy'][$_s] = ($_d) ? $_d : 'ASC';
			}
		}
		$this->_output .= "<thead>\n<tr>\n";

		if(isset($this->_options['selectable']) && $this->_options['selectable'])
		{
			$this->_output .= "<th class=\"nobg\"></th>\n";
		}

		foreach($this->_fields as $key => $field)
		{
			$this->_output .= '<th>';

			if($this->_options['sortable'] && (!isset($this->_options['sortableFields'][$key]) || $this->_options['sortableFields'][$key]))
			{
				$url = $this->_getUrl();
				$test = is_int($key) ? $field : $key;
				$this->_output .= '<a class="sorter"';
				$dir = (isset($this->_options['orderBy'][$test]) && $this->_options['orderBy'][$test] == 'ASC') ? 'DESC' : 'ASC';
				$this->_output .= ' href="' . $url . '/_s/' . $test . '/_d/' . $dir . '">';

				$this->_output .= $field;
				if(isset($this->_options['orderBy'][$test]))
				{
					$this->_output .= ($this->_options['orderBy'][$test] == 'DESC') ? ' &uArr;' : ' &dArr;';
				}
				$this->_output .= '</a>';
			}
			else
			{
				$this->_output .= $field;
			}
			$this->_output .= "</th>\n";
		}
		$this->_output .= "</tr>\n</thead>\n";
	}

	protected function _processCallback($callback, $params)
	{
		if ( ! is_callable($callback) )
		{
			throw new Maco_PGrid_Renderer_Exception($value['function'] . ' not callable');
		}

		if ( isset($params) && is_array($params) ) {
			$toReplace = $params;
			$toReplaceArray = array();
			$toReplaceObj = array();

			foreach ( $toReplace as $key => $rep ) {
				if ( is_scalar($rep) || is_array($rep) ) {
					$toReplaceArray[$key] = $rep;
				} else {
					$toReplaceObj[$key] = $rep;
				}
			}
		} else {
			return call_user_func($callback);
		}

		for ( $i = 0; $i <= count($toReplace); $i ++ ) {
			if ( isset($toReplaceArray[$i]) ) {
				$toReplace[$i] = $toReplaceArray[$i];
			} elseif ( isset($toReplaceObj[$i]) ) {
				$toReplace[$i] = $toReplaceObj[$i];
			}
		}

		return call_user_func_array($callback, $toReplace);
	}

	/**
	 *
	 * @param array $fields
	 * @return array
	 */
	protected function _prepareReplace ($fields)
	{
		return array_map(create_function('$value', 'return "{{{$value}}}";'), $fields);
	}

	public function buildRow($data, $i)
	{
		$this->_output .= "<tr class=\"" . $this->_options['rowAltClass'][$i%2] . "\">\n";

		if(isset($this->_options['selectable']) && $this->_options['selectable'])
		{
			$class = ($i % 2) ? "specalt" : "spec";
			$this->_output .= "<th class=\"$class\"></th>\n";
		}

		foreach($this->_fields as $key => $value)
		{
			if(is_int($key))
			{
				$val = isset($data[$value]) ? $data[$value] : 'unknown';
				$this->_output .= '<td>' . $val . "</td>\n";
			}
			else
			{
				if(isset($this->_options['callbacks'][$key]))
				{
					$function = $this->_options['callbacks'][$key][0];
					$params = $this->_options['callbacks'][$key][1];
					//$this->_processCallback($function, $params);
				}
				$this->_output .= '<td>' . $data[$key] . "</td>\n";
			}
		}
		$this->_output .= "</tr>\n";
	}

	/**
	 * Replaces {{field}} for the actual field value
	 * @param  $item
	 * @param  $key
	 * @param  $text
	 */
	protected function _replaceSpecialTags (&$item, $key, $text)
	{
		$item = str_replace($text['find'], $text['replace'], $item);
	}

	protected function _buildScripts()
	{
		$ajax = "";
		if(!Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest())
		{
			if($this->_options['useAjax'])
			{
				$view = $this->_options['useAjax'];
				//$this->headScript()->appendFile($this->baseUrl('/js/common/grid.js'));
				$grid_id = $this->_options['id'];
				$url = $this->_getUrl();

				if(!self::$_ajaxClassPrinted)
				{

					$function =<<<EOJ
    var PGridAjax = new Class({
        Implements: [Options, Events],
        options: {
            'onComplete': \$empty(),
            'onRequest': \$empty(),
            'onShow': \$empty(),
        },
        initialize: function(table, options) {
            this.setOptions(options);
            this.t = document.id(table);
            this.tableId = this.t.get('id');
            this.req = new Request.HTML({
                url: '$url',
                update: this.t.getParent(),
                onSuccess: this._success.bind(this),
                useSpinner: true,
                spinnerOptions: {
                    message: 'caricamento...',
                }
            });
            this.powerUpLinks();
        },
        _success: function(html){
            this.t = document.id(this.tableId);
            this.powerUpLinks();
        },
        powerUpLinks: function(){
            this.t.getElements('a.sorter').addEvent('click', this._send.bind(this));
        },
        _send: function(e){
            e.preventDefault();
            var u = e.target.get('href');
            var a = u.match(/\/_s\/[a-z|0-9|_-]+\//i);
            var _s = a[0].substring(4, a[0].length - 1);
            a = u.match(/\/_d\/[a-z]+\/*/i);
            var _d = (a) ? a[0].substring(4, a[0].length) : 'ASC';
            
            this.req.post({
                'format': 'html',
                '_s' : _s,
                '_d' : _d
            });
        }
    });
EOJ;
					self::$_ajaxClassPrinted = true;
				}
				$ajax = "var gt = new PGridAjax('$grid_id');";
			}

			$function .= <<<EOJ
    window.addEvent('domready', function(){
/*        var t = document.id('$grid_id');
        t.getElements('tbody tr').addEvent('click', function(e){
            e.preventDefault();
            alert('tr');
        });
        */
        $ajax
        
    });
EOJ;

        $view->headScript()->appendScript($function);
		}
	}

	protected function _buildPagination()
	{
		$front = Zend_Controller_Front::getInstance();
		$baseUrl = $front->getBaseUrl();

		//$pages = $this->_paginator->getPages();
		//$pages = $this->_source->getPages();
		$pages = new stdClass();
		$pages->current = 1;
		$pages->next = 2;
		$pages->last = 2;
		$pages->firstItemNumber = 1;
		$pages->lastItemNumber = 2;
		$pages->totalItemCount = 3;
		$pages->pageCount = 2;

		$out = '<div class="pagination">';
		if($pages->current > 1)
		{
			$lnkf = '<a href="javascript:application.changePage(\'' . $this->_options['id'] . '\', 1)" title="vai alla prima pagina">';
			$lnkp = '<a href="javascript:application.changePage(\'' . $this->_options['id'] . '\', ' . ($pages->current - 1) . ')" title="vai alla pagina precedente">';
			$lnk_ = '</a>';
			$f = 'go-first.gif';
			$p = 'go-previous.gif';
		}
		else
		{
			$f = 'go-first-d.gif';
			$p = 'go-previous-d.gif';
			$lnkf = '';
			$lnkp = '';
			$lnk_ = '';
		}

		if($pages->current < $pages->last)
		{
			$lnkl = '<a href="javascript:application.changePage(\'' . $this->_options['id'] . '\', ' . $pages->last . ')" title="vai all\'ultima pagina">';
			$lnkn = '<a href="javascript:application.changePage(\'' . $this->_options['id'] . '\', ' . $pages->next . ')" title="vai alla pagina successiva">';
			$lnk_n = '</a>';
			$l = 'go-last.gif';
			$n = 'go-next.gif';
		}
		else
		{
			$l = 'go-last-d.gif';
			$n = 'go-next-d.gif';
			$lnkl = '';
			$lnkn = '';
			$lnk_n = '';
		}

		$out .= $lnkf . '<img src="' . $baseUrl . '/img/' .$f . '" />' . $lnk_;
		$out .= $lnkp . '<img src="' . $baseUrl . '/img/' .$p . '" />' .$lnk_;
		$out .= ' <input type="text" size="1" value="' . $pages->current . '" onchange="application.changePage(\'' . $this->_options['id'] . '\', this.value)"> di ' . $pages->pageCount . ' ';
		$out .= $lnkn . '<img src="' . $baseUrl . '/img/' .$n . '" />' . $lnk_n;
		$out .= $lnkl . '<img src="' . $baseUrl . '/img/' .$l . '" />' .$lnk_n;
		$out .= '<span class="more_info">(' . $pages->firstItemNumber . ' - '
		. $pages->lastItemNumber . ' di ' . $pages->totalItemCount . ')</span>';

		$out .='</div>';

		$out .= '<div class="pagination_info"><strong>' . $pages->totalItemCount . '</strong> utenti su <strong>' . $this->_source->getTotalRecords() . '</strong> totali - pagina <strong>' . $pages->current . '/' . $pages->pageCount . '</strong></div>';

		$this->_output .= $out;;
	}

	public function setSource(Maco_PGrid_Source_Interface $source, array $fields)
	{
		$this->_source = $source;
		$this->_fields = $fields;
	}

	public function execute()
	{
		$this->_buildScripts();
		$this->_output .= '<table ';
		if($this->_options['id'])
		{
			$this->_output .= 'id = "' . $this->_options['id'] . '"';
		}
		$this->_output .= ' class="' . implode(" ", $this->_options['class']) . "\">\n";

		$this->_output .= "<caption>\n";
		$this->_buildPagination();
		$this->_output .= "</caption>\n";

		$this->buildTitles();

		$this->_output .= "<tbody>\n";

		$total = $this->_source->getTotalRecords();
		for ($i = 0; $i < $total; $i++)
		{
			$this->buildRow($this->_source->getRecord($i), $i);
		}

		$this->_output .= "</tbody>\n";
		$this->_output .= "</table>\n";

		return $this->_output;
	}
}
