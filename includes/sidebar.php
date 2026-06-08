<?php
function menu_icon($file, $fa)
{
    return '<span class="menu-icon"><img src="' . e(asset_url('icons/' . $file)) . '" alt="" loading="lazy" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';"><i class="' . e($fa) . '" style="display:none"></i></span>';
}
