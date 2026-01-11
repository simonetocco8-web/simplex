<?php

class Maco_DaGrid_Render_Html_Paginator_Default extends Maco_DaGrid_Render_Html_Renderer implements Maco_DaGrid_Render_Html_Paginator_Interface
{
	/**
	 * Page label for the request
	 *
	 * @var string
	 */
	protected $_pageLabel = 'page';

	/**
	 * The provider for this pagination renderer
	 *
	 * @var Maco_DaGrid_Paginator_Default
	 */
	protected $_provider;

	/**
	 * Costruttore
	 *
	 * @param Maco_DaGrid_Paginator_Default $provider
	 */
	public function __construct(Maco_DaGrid_Paginator_Default &$provider)
	{
		parent::__construct();
		$this->setProvider($provider);
		$this->_defaultTpl = 'paginator/default.phtml';
	}

	/**
	 * Sets the page label
	 *
	 * @param string $label
	 */
	public function setPageLabel($label)
	{
		$this->_pageLabel = $label;
	}

	/**
	 * Returns the page label
	 *
	 * @return string
	 */
	public function getPageLabel()
	{
		return $this->_pageLabel;
	}

	public function setProvider(Maco_DaGrid_Paginator_Default &$provider)
	{
		$this->_provider = $provider;
	}

	public function getFirstPageUri()
	{
		return $this->getUriForPage(1);
	}

	public function getPreviousPageUri()
	{
		$page = $this->_provider->getCurrentPage() - 1;
		return $this->getUriForPage($page);
	}

	public function getNextPageUri()
	{
		$page = $this->_provider->getCurrentPage() + 1;
		return $this->getUriForPage($page);
	}

	public function getLastPageUri()
	{
		$page = $this->_provider->getTotalPages();
		return $this->getUriForPage($page);
	}

	public function getUriForPage($pageNumber)
	{
		$uri = $this->_provider->getGridRawUri();
		$clean = $this->_cleanUri($uri);

        $pagePart = $this->_pageLabel . '=';
        $pattern = '/(\?|&)(' . $pagePart . ')(\d+)/';

        if(preg_match($pattern, $clean))
        {
            $replacement = '${1}${2}' . $pageNumber;

            return preg_replace($pattern, $replacement, $clean);
        }

        $sep = strpos($clean, '?') ? '&' : '?';

		return $clean . $sep . $this->_pageLabel . '=' . $pageNumber;
	}

	public function getPaginationUriWithoutPageNumber()
	{
		$uri = $this->_provider->getGridRawUri();
		$clean = $this->_cleanUri($uri);

        $pagePart = $this->_pageLabel . '=';

        if(strpos($clean, $pagePart))
        {
            $pattern = '/(\?|&)(' . $pagePart . ')(\d+)/';
            $replacement = '${1}${2}';

            return preg_replace($pattern, $replacement, $clean);
        }

        $sep = strpos($clean, '?') ? '&' : '?';

		return $clean . $sep . $this->_pageLabel . '=';
	}

	public function _cleanUri($uri)
	{
		$pageLabel = $this->getPageLabel();
		$uri = preg_replace('/(\?|&)' . $pageLabel . '=[0-9|a-z|A-Z|-|_]*/', '', $uri);
		return $uri;
	}
}
