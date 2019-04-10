<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le titre est obligatoire")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Le contenu est obligatoire")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publicationDate;

    /**
     * Le fetch="EAGER" fait que quand on requête les articles, une jointure est faite sur
     * la table category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="articles", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="La catégorie est obligatoire")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="articles", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Image(mimeTypesMessage="Le fichier doit être une image",
     *      maxSize="200k", maxSizeMessage="L'image ne doit pas faire plus de 200Ko")
     */
    private $image;

    /**
     * cascade={"remove"} : quand on supprime un article, ça supprime ses commentaires
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="article", cascade={"remove"})
     * Les commentaires seront triés par date de publication décroissante
     * en lazy loading
     * @ORM\OrderBy({"publicationDate": "DESC"})
     */
    private $comments;

    public function __construct()
    {
        $this->setPublicationDate(new \DateTime());
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return Article
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function __toString()
    {
        return $this->title;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }
}
