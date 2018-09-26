<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            $text = replace($text, 'quote', 'summary_html', Quote::renderHtml($_quoteFromRepository));

            $text = replace($text, 'quote', 'summary', Quote::renderText($_quoteFromRepository));

            $text = replace($text, 'quote', 'destination_name', $destinationOfQuote->countryName);

            $text = replace($text, 'quote', 'destination_link', $usefulObject->url . '/' . $destinationOfQuote->countryName . '/quote/' . $_quoteFromRepository->id);
        }       
    
        $text = replace($text, 'user', 'first_name', ucfirst(mb_strtolower($_user->firstname)));

        return $text;
    }

    private function replace($text, $concern, $field, $newText) 
    {
        $oldText = '['.$concern.':'.$field.']';

        if(strpos($text, $oldText) !== false) {
            $text = str_replace(
                $oldText,
                $newText,
                $text
            );
        }

        return $text;
    }

}