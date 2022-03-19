<?php
declare(strict_types=1);

namespace laocc\tencent;

use esp\core\Library;

class _Base extends Library
{
    protected $conf;

    public function _init(array $option = [])
    {
        $this->conf = $option;
    }

}