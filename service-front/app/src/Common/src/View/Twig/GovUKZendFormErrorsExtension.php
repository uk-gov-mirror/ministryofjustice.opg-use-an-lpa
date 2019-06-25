<?php

declare(strict_types=1);

namespace Common\View\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zend\Form\ElementInterface;
use Zend\Form\FormInterface;

class GovUKZendFormErrorsExtension extends AbstractExtension
{
    const THEME_FILE = '@partials/govuk_error.html.twig';

    /**
     * @return array
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('govuk_error', [$this, 'errorMessage'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('govuk_error_summary', [$this, 'errorSummary'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param Environment $twigEnv
     * @param ElementInterface $element
     * @return string
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function errorMessage(Environment $twigEnv, ElementInterface $element) : string
    {
        $template = $twigEnv->load(self::THEME_FILE);

        $messages = $this->flattenMessages($element->getMessages());

        return $template->renderBlock('error_message', [
            'id'     => $element->getName(),
            'errors' => $messages,
        ]);
    }

    /**
     * @param Environment $twigEnv
     * @param FormInterface $form
     * @return string
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function errorSummary(Environment $twigEnv, FormInterface $form) : string
    {
        $template = $twigEnv->load(self::THEME_FILE);

        $messages = $form->getMessages();

        //  Flatten each set of messages for each input
        foreach ($messages as $inputName => $inputMessages) {
            $messages[$inputName] = $this->flattenMessages($inputMessages);
        }

        return $template->renderBlock('error_summary', [
            'errors' => $messages,
        ]);
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessages(array $messages) : array {
        $messagesToPrint = [];

        array_walk_recursive($messages, function ($item) use (&$messagesToPrint) {
            $messagesToPrint[] = $item;
        });

        return $messagesToPrint;
    }
}