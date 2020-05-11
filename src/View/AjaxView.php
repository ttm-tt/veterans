<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\View;

/**
 * A view class that is used for AJAX responses.
 * Currently only switches the default layout and sets the response type -
 * which just maps to text/html by default.
 */
class AjaxView extends \Cake\View\AjaxView
{
}
