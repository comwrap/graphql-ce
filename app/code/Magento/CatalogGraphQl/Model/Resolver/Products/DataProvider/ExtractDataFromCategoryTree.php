<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\CatalogGraphQl\Model\Category\Hydrator;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Extract data from category tree
 */
class ExtractDataFromCategoryTree
{
    /**
     * @var Hydrator
     */
    private $categoryHydrator;

    /**
     * @param Hydrator $categoryHydrator
     */
    public function __construct(
        Hydrator $categoryHydrator
    ) {
        $this->categoryHydrator = $categoryHydrator;
    }

    /**
     * Extract data from category tree
     *
     * @param \Iterator $iterator
     * @return array
     */
    public function execute(\Iterator $iterator): array
    {
        $tree = [];
        while ($iterator->valid()) {
            /** @var CategoryInterface $category */
            $category = $iterator->current();
            $iterator->next();
            $nextCategory = $iterator->current();

            $pathElements = explode("/", $category->getPath());


            $level = $this->grabLevel($pathElements, 0);
            if (!empty($tree)){
                $tree = $this->array_merge_recursive_ex($level, $tree);
            }else{
                $tree = $level;
            }

            //            for ($level = 1; $level <= $category->getLevel(); $level++){
//            if ($category->getLevel() >= 2)
//            $element[$pathElements[1]] = $this->grabLevel(2, (int) $category->getLevel(), $pathElements);
//                $tree[$category->getId()] = $this->categoryHydrator->hydrateCategory($category);
//                $tree[$category->getId()]['model'] = $category;
//            }

//            if ($nextCategory && (int) $nextCategory->getLevel() !== (int) $category->getLevel()) {
//                $tree[$category->getId()]['children'] = $this->execute($iterator);
//            }
        }

        return $tree;
    }
    public function array_merge_recursive_ex(array & $array1, array & $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value)
        {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
            {
                $merged[$key] = $this->array_merge_recursive_ex($merged[$key], $value);
            } else if (is_numeric($key))
            {
                if (!in_array($value, $merged))
                    $merged[] = $value;
            } else
                $merged[$key] = $value;
        }

        return $merged;
    }

    public function grabLevel($elements, $index){

        $tree = [];
        $tree[$elements[$index]]['id'] = $elements[$index];
        $currentIndex = $index;
        $index++;
        if (isset($elements[$index])){
            $tree[$elements[$currentIndex]]['children'] = $this->grabLevel($elements, $index);
        }
        return $tree;

    }
}
