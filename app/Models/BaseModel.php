<?php
namespace App\Models;


/**
 * 開催情報
 */
abstract class BaseModel
{
    /** Jsonデータを設定 */
    public function setJsonData($json)
    {
        foreach($json as $key => $val)
        {
            if(property_exists($this, $key))
            {
                if($this->setAnotherJson($key, $val)){
                }
                elseif(is_array($this->{$key}))
                {
                    $this->setArrayJson($key, $val);
                }
                else
                {
                    $this->{$key} = $val;
                }
            }
        }
    }

    abstract protected function setArrayJson($key, $val);
    abstract protected function setAnotherJson($key, $val);

}
