<?php

/*
 * @copyright   2021 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Validator\Constraints;

use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\LeadListRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SegmentInUseValidator extends ConstraintValidator
{
    /**
     * @var LeadListRepository
     */
    public $segmentRepository;

    public function __construct(LeadListRepository $segmentRepository)
    {
        $this->segmentRepository = $segmentRepository;
    }

    /**
     * @param LeadList $leadList
     */
    public function validate($leadList, Constraint $constraint): void
    {
        if (!$constraint instanceof SegmentInUse) {
            throw new UnexpectedTypeException($constraint, SegmentInUse::class);
        }

        if (!$leadList->getId() || $leadList->getIsPublished()) {
            return;
        }

        $lists = $this->segmentRepository->getSegmentsByFilter(LeadList::MEMBERSHIP_FILTER_FIELD, $leadList->getId());

        if (count($lists)) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->addViolation();
        }
    }
}
