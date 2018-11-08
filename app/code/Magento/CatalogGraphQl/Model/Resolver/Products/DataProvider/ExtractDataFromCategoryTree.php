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
    const START_CATEGORY_FETCH_LEVEL = 1;

    /**
     * @var Hydrator
     */
    private $categoryHydrator;

    /**
     * @var CategoryInterface;
     */
    private $iteratingCategory;

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

            $pathElements = explode("/", $category->getPath());
            $this->iteratingCategory = $category;

            $currentLevelTree = $this->explodePathToArray($pathElements, self::START_CATEGORY_FETCH_LEVEL);
            if (empty($tree)) {
                $tree = $currentLevelTree;
            }
            $tree = $this->mergeCategoriesTrees($currentLevelTree, $tree);
        }

        return $tree;
    }

    /**
     * Merge together complex categories trees
     *
     * @param array $tree1
     * @param array $tree2
     * @return array
     */
    private function mergeCategoriesTrees(array &$tree1, array &$tree2): array
    {
        $mergedTree = $tree1;
        foreach ($tree2 as $currentKey => &$value){
            if (is_array($value) && isset($mergedTree[$currentKey]) && is_array($mergedTree[$currentKey])){
                $mergedTree[$currentKey] = $this->mergeCategoriesTrees($mergedTree[$currentKey], $value);
            }else{
                $mergedTree[$currentKey] = $value;
            }
        }
        return $mergedTree;
    }

    /**
     * Recursive method to generate tree for one category path
     *
     * @param $pathElements
     * @param $index
     * @return array
     */
    private function explodePathToArray($pathElements, $index): array
    {

        $tree = [];
        $tree[$pathElements[$index]]['id'] = $pathElements[$index];
        if ($index == count($pathElements) - 1){
            $tree[$pathElements[$index]] = $this->categoryHydrator->hydrateCategory($this->iteratingCategory);
            $tree[$pathElements[$index]]['model'] = $this->iteratingCategory;
        }
        $currentIndex = $index;
        $index++;
        if (isset($pathElements[$index])){
            $tree[$pathElements[$currentIndex]]['children'] = $this->explodePathToArray($pathElements, $index);
        }
        return $tree;

    }
}
