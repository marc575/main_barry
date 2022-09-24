<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Emails
 *
 * @ORM\Table(name="coupon_image")
 * @ORM\Entity
 */
class CouponImage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=254, nullable=false)
     */
    private $fileName;

    /**
     * @var Coupon
     *
     * @ORM\ManyToOne(targetEntity="Coupon")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="coupons", referencedColumnName="id")
     * })
     */
    private $coupons;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return Coupon
     */
    public function getCoupons(): Coupon
    {
        return $this->coupons;
    }

    /**
     * @param Coupon $coupons
     */
    public function setCoupons(Coupon $coupons): void
    {
        $this->coupons = $coupons;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }


}
