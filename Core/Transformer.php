<?php

namespace System\Core;

class Transformer
{
    private $data;
    private $isResource;
    private $resources;
    public function __construct($resources)
    {
        $this->resources = $resources;
        if (is_array($resources)) {
            $this->isResource = 'list';
        } else {
            $this->isResource = 'detail';
        }
    }
    public function getOutput()
    {
        if ($this->isResource == 'detail') {
            $this->setAttribute($this->resources);
            $this->data = $this->response();
        } else if ($this->isResource == 'list') {
            foreach ($this->resources as $item) {
                $this->setAttribute($item);
                $this->data[] = $this->response();
            }
        }
        return $this->data;
    }
    private function setAttribute($obj) {
        $attributes = get_object_vars($obj);
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
