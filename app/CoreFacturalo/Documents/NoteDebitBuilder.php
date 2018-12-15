<?php

namespace App\CoreFacturalo\Documents;

use App\Models\Document;

class NoteDebitBuilder extends DocumentBuilder
{
    public function save($inputs)
    {
        $document = array_key_exists('document', $inputs)?$inputs['document']:$inputs;
        $this->saveDocument($document);

        $document_base = array_key_exists('document_base', $inputs)?$inputs['document_base']:$inputs;
        $this->document->note()->create($document_base);
    }

    public function getDocument()
    {
        return Document::find($this->document->id);
    }
}