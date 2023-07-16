<?php
/**
 * Recipe voter.
 */

namespace App\Security\Voter;

use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class RecipeVoter.
 */
class RecipeVoter extends Voter
{
    /**
     * Edit permission.
     */
    public const EDIT = 'POST_EDIT';
    /**
     * View permision.
     */
    public const VIEW = 'POST_VIEW';

    /**
     * Delete permission.
     */
    public const DELETE = 'POST_DELETE';

    /**
     * Security helper.
     */
    private Security $security;

    /**
     * Constructor.
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Recipe;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already
     * passed the "supports()" method check.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    /**
     * Checks if user can view recipe.
     */
    private function canView(Recipe $recipe, User $user): bool
    {
        return true;
    }

    /**
     * Checks if user can edit the recipe.
     */
    private function canEdit(Recipe $recipe, User $user): bool
    {
        return $recipe->getAuthor() === $user;
    }

    /**
     * Checks if user can delete recipe.
     */
    private function canDelete(Recipe $recipe, User $user): bool
    {
        return $recipe->getAuthor() === $user;
    }
}
