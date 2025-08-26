<?php

// src/Dto/ContactMessageDto.php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContactMessageDto
{
    #[Assert\NotBlank(message: 'Name darf nicht leer sein.')]
    public string $name;

    #[Assert\NotBlank(message: 'E-Mail darf nicht leer sein.')]
    #[Assert\Email(message: 'Bitte eine gültige E-Mail-Adresse angeben.')]
    public string $email;

    #[Assert\NotBlank(message: 'Nachricht darf nicht leer sein.')]
    #[Assert\Length(min: 10, minMessage: 'Die Nachricht muss mindestens {{ limit }} Zeichen lang sein.')]
    public string $message;
}
