<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-06
 * @version 1.0 2009-03-07
 */
namespace Easy\Collections\Generic;

use Easy\Collections\Stack;
use Easy\Generics\Type;

class GenericStack extends Stack implements GenericInterface
{

    protected $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function contains($item)
    {
        if ($this->isItemFromTheType($item) == false) {
            return false;
        }
        return parent::Contains($item);
    }

    public function push($item)
    {
        if ($this->isItemFromTheType($item) == true)
            parent::Push($item);
    }

    public function pushMultiple($items)
    {
        if ($items instanceof GenericInterface && $items instanceof CollectionBase) {
            $genericsCount = $this->NumberOfTypes();
            if ($genericsCount == $items->NumberOfTypes()) {
                $arr1 = $this->GetTypes();
                $arr2 = $items->GetTypes();
                for ($i = 0; $i < $genericsCount; $i++) {
                    if ($arr1[$i]->Equals($arr2[$i]) == false)
                        return;
                }

                $this->addMultiple($items);
            }
        } else {
            $res = new Collection();
            if ($items instanceof IteratorAggregate || is_array($items)) {
                foreach ($items AS $value) {
                    if ($this->isItemFromTheType($value, false))
                        $res->add($value);
                }
            } else {
                throw new InvalidArgumentException(__('Items must be either a Collection or an array'));
            }
            $this->addMultiple($res);
        }
    }

    protected function isItemFromTheType($item, $ex = true)
    {
        $result = $this->type->IsItemFromType($item);
        if ($result == false && $ex == true)
            throw new InvalidArgumentException(__('You can only use items of the type: ' . $this->type));
        return $result;
    }

    public function GetType()
    {
        return $this->type;
    }

    public function GetTypes()
    {
        return array($this->type);
    }

    public static function NumberOfTypes()
    {
        return 1;
    }

}