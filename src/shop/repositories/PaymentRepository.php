<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use MicheleAngioni\PhalconRepositories\AbstractCollectionRepository;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Shop\Models\Payment;
use VitesseCms\Shop\Models\PaymentIterator;

class PaymentRepository extends AbstractCollectionRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Payment
    {
        Payment::setFindPublished($hideUnpublished);

        /** @var Payment $payment */
        $payment = Payment::findById($id);
        if (is_object($payment)):
            return $payment;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): PaymentIterator {
        Payment::setFindPublished($hideUnpublished);

        if($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Payment::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;

        return new PaymentIterator(Payment::findAll());
    }
}
