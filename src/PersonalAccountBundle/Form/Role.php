<?php
namespace PersonalAccountBundle\Form;

class Role
{
    const CAPTION_ADMIN = 'Администратор';
    const CAPTION_STUDENT = 'Ученик';
    const CAPTION_TEACHER = 'Учитель';

    protected static $captions = [
        self::CAPTION_ADMIN,
        self::CAPTION_STUDENT,
        self::CAPTION_TEACHER
    ];

    public static function getCaptionMap()
    {
        return array_combine(self::$captions, self::$captions);
    }
}