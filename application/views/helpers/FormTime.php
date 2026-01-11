<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 09/04/13
 * Time: 16.46
 * To change this template use File | Settings | File Templates.
 */


/**
 * Abstract class for extension
 */
// require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a "time" (hours and minutes) element
 */
class Zend_View_Helper_FormTime extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'text' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formTime($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        $classes = isset($attribs['class']) ? $attribs['class'] : '';
        $classes .= ' input-fixed validate-integer';
        $attribs = array_merge($attribs, array('class' => $classes));

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        $parsed = Maco_Utils_Time::fromValue($value);

        $xhtml = 'h <input type="text"'
            . ' name="' . $this->view->escape($name) . '_hour"'
            . ' id="' . $this->view->escape($id) . '_hour"'
            . ' value="' . $this->view->escape($parsed['hours']) . '"'
            . $disabled
            . $this->_htmlAttribs($attribs)
            . ' style="width: 20px"'
            . $endTag;

        $xhtml .= ' m <select'

        //$xhtml .= ' e <input type="text"'
            . ' name="' . $this->view->escape($name) . '_minute"'
            . ' id="' . $this->view->escape($id) . '_minute"'
        //    . ' value="' . $this->view->escape($value) . '"'
            . $disabled
            . $this->_htmlAttribs($attribs)
            . '>';

        for($i = 0; $i <=50; $i += 10)
        {
            $selected = ($i == $parsed['minutes']) ? 'selected="selected"' : '';
            $xhtml .= '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
        }

        $xhtml .= '</select>';

        return $xhtml;
    }
}
