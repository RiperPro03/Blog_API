# API REST pour la gestion d'articles

L'objectif général de ce projet est de proposer une solution pour la gestion d'articles de blogs. Il s'agit en particulier de concevoir le backend de la solution, c'est-à-dire la partie cachée aux utilisateurs de l'application. La solution devra s'appuyer sur une architecture orientée ressources, c'est-à-dire sur une ou plusieurs API REST.
## Fonctions principales

- La publication, la consultation, la modification et la suppression des articles de blogs. Un article est caractérisé, a minima, par sa date de publication, son auteur et son contenu.

- L'authentification des utilisateurs souhaitant interagir avec les articles. Cette fonctionnalité devra s'appuyer sur les JSON Web Token (JWT). Un utilisateur est caractérisé, a minima, par un nom d'utilisateur, un mot de passe et un rôle (moderator ou publisher).

- La possibilité de liker/disliker un article. La solution doit permettre de retrouver quel(s) utilisateur(s) a liké/disliké un article.

## Gestion des restrictions d'accès

- Un utilisateur authentifié avec le rôle moderator peut :

    - Consulter n'importe quel article. Un utilisateur moderator doit accéder à l'ensemble des informations décrivant un article : auteur, date de publication, contenu, liste des utilisateurs ayant liké l'article, nombre total de like, liste des utilisateurs ayant disliké l'article, nombre total de dislike.
    - Supprimer n'importe quel article.


- Un utilisateur authentifié avec le rôle publisher peut :

    - Poster un nouvel article.
    - Consulter ses propres messages.
    - Consulter les messages publiés par les autres utilisateurs. Un utilisateur publisher doit accéder aux informations suivantes relatives à un article : auteur, date de publication, contenu, nombre total de like, nombre total de dislike.
    - Modifier les articles dont il est l'auteur.
    - Supprimer les articles dont il est l'auteur.
    - Liker/disliker les articles publiés par les autres utilisateurs.

- Un utilisateur non authentifié peut :
    - Consulter les messages existants. Seules les informations suivantes doivent être disponibles : auteur, date de publication, contenu.


# Authors

- [Christopher Asin](https://www.github.com/RiperPro03) 


- [Henri JEZEQUEL](https://github.com/HenriJez)


# API Auth

### Obtenir un token JWT

```http
  POST /api/auth/
```
| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `username` | `string` | **Required**. Votre username |
| `password` | `string` | **Required**. Votre password |


# API gestiArticle

### Récupérer tout les articles

```http
  GET /api/gestiArticle/
```

### Récupérer les articles à partir d'un id

```http
  GET /api/gestiArticle/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. Id de l'article à recherher |


### Créer un article
Role requis: publisher ou moderator

```http
  POST /api/gestiArticle/
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `title`      | `string` | **Required**. Titre de l'article |
| `content`      | `string` | **Required**. Contenu de l'article |

### Modifier un article
Role requis: publisher ou moderator

```http
  PUT /api/gestiArticle/${id}
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. Id de l'article à modifier |
| `title`      | `string` | **Required**. Titre de l'article |
| `content`      | `string` | **Required**. Contenu de l'article |

### Supprimer un article
Role requis: publisher ou moderator

```http
  DELETE /api/gestiArticle/${id}
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. Id de l'article à supprimer |

### Liker un article
```http
  POST /api/gestiArticle/like/${id}
```	
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. Id de l'article à liker |
| `like`      | `boolean` | **Required**. true pour liker, false pour disliker |
