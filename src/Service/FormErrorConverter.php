<?php

namespace App\Service;

use Symfony\Component\Form\Form;

class FormErrorConverter
{
    public function getMessage(Form $form): array
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $message = $this->getMessage($child);
                $errors[$child->getName()] = $this->getMessage($child);
                $errors[$child->getName()] = array_unique($errors[$child->getName()], SORT_REGULAR);
                if (is_array($errors[$child->getName()][0]) && count($errors[$child->getName()][0]) == 1) {
                    $errors[$child->getName()] = $errors[$child->getName()][0];
                }
            }
        }

        return $errors;
    }
}
