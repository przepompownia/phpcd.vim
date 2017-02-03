<?php

namespace PHPCD\View;

class VimMenuItem
{
    private $output = [
        'icase' => 1,
    ];

    /**
     * @return $this
     */
    public function setWord($word)
    {
        $this->output['word'] = $word;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAbbr($abbr)
    {
        $this->output['abbr'] = $abbr;

        return $this;
    }

    /**
     * @return $this
     */
    public function setInfo($info)
    {
        $this->output['info'] = $info;

        return $this;
    }

    /**
     * @return $this
     */
    public function setKind($kind)
    {
        $this->output['kind'] = $kind;

        return $this;
    }

    /**
     * @return $this
     */
    public function setIcase($icase)
    {
        $this->output['icase'] = $icase;

        return $this;
    }

    public function render()
    {
        return $this->output;
    }
}
