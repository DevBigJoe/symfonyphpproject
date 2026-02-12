<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum ArticleType: string
{
    case Explanation = 'Erklärung';
    case ExampleExam = 'Beispielklausur';
    case FlashCards = 'Karteikarten';
    case Exercises = 'Übung';
    case LernSheet = 'Lernzettel';
    case Mindmap = 'Mindmap';
    case Transcript = 'Vorlesungsmitschrift';

    public function getTranslationKey(): string
    {
        return 'article_type.' . $this->name;
    }
}
