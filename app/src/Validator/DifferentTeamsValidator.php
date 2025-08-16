<?php

namespace App\Validator;

use App\Entity\Game;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DifferentTeamsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DifferentTeams) {
            throw new UnexpectedTypeException($constraint, DifferentTeams::class);
        }

        if (!$value instanceof Game) {
            return;
        }

        $homeTeam = $value->getHomeTeam();
        $awayTeam = $value->getAwayTeam();

        // Only validate if both teams are set
        if ($homeTeam && $awayTeam && $homeTeam->getId() === $awayTeam->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
