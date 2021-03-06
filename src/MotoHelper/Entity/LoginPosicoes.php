<?php

namespace MotoHelper\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginPosicoes
 * @ORM\Entity
 * @ORM\Table(name="login_posicoes")
 */
class LoginPosicoes
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Login
     * @ORM\OneToOne(targetEntity="Login",inversedBy="posicao")
     * @ORM\JoinColumn(name="id_login",referencedColumnName="id", onDelete="CASCADE")
     */
    private $login;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $latitude;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $longitude;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLatLong()
    {
        return "$this->latitude,$this->longitude";
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function toArray()
    {
        return [
            "label" => $this->label,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude
        ];
    }
}
