<?php
/**
 * Recipe service interface.
 */

namespace App\Service;

use App\Entity\Recipe;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface.
 */
interface RecipeServiceInterface
{
    /**
     * Get paginated list.
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     */
    public function save(Recipe $recipe): void;

    /**
     * Delete recipe.
     */
    public function delete(Recipe $recipe): void;
}
