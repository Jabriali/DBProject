<?php
    # Funzioni utente
    function estraiDatiUtente($pdo, $username) {
        try {
            $statement = $pdo->prepare("SELECT * FROM utente WHERE username = :username LIMIT 1");
            $statement->bindValue(':username', $username);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC)[0];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function verificaUtenteDuplicato($pdo, $username) {
        try {
            $statement = $pdo->prepare("SELECT username FROM utente WHERE username = :username LIMIT 1");
            $statement->bindValue(':username', $username);
            $statement->execute();
            return $statement->fetchColumn(); # Ottengo i dati sottoforma di array
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function verificaEmailDuplicata($pdo, $email) {
        try {
            $statement = $pdo->prepare("SELECT email FROM utente WHERE email = :email LIMIT 1");
            $statement->bindValue(':email', $email);
            $statement->execute();
            return $statement->fetchColumn();
        } catch (\Throwable $th) {
            throw $th;
        }    
    }

    function verificaTelefonoDuplicato($pdo, $telefono) {
        try {
            $statement = $pdo->prepare("SELECT telefono FROM utente WHERE telefono = :telefono LIMIT 1");
            $statement->bindValue(':telefono', $telefono);
            $statement->execute();
            return $statement->fetchColumn();
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    function verificaDocuDuplicato($pdo, $docu_id) {
        try {
            $statement = $pdo->prepare("SELECT docu_id FROM utente WHERE docu_id = :docu LIMIT 1");
            $statement->bindValue(':docu', $docu_id);
            $statement->execute();
            return $statement->fetchColumn();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiPasswordHash($pdo, $username) {
        try {
            $statement = $pdo->prepare("SELECT password_hash FROM utente WHERE username = :username LIMIT 1");
            $statement->bindValue(':username', $username);
            $statement->execute();
            return $statement->fetchColumn();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    # Funzioni blog
    function estraiBlogUtente($pdo, $username) {
        try {
            $statement = $pdo->prepare("SELECT * FROM blog WHERE blog_founder = :username");
            $statement->bindValue(':username', $username);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiTemplate($pdo) {
        try {
            $statement = $pdo->prepare("SELECT template_name FROM template");
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiTemplateInfo($pdo, $template_name) {
        try {
            $statement = $pdo->prepare("SELECT * FROM template WHERE template_name = :template_name");
            $statement->bindValue(':template_name', $template_name);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    function estraiBlogInfo($pdo, $blog_id) {
        try {
            $statement = $pdo->prepare("SELECT * FROM blog WHERE blog_id = :blog_id");
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiBlogAmministrati($pdo, $username) {
        try {
                $statement = $pdo->prepare("SELECT coopera.blog_id, blog_titolo, blog_descrizione, creazione_data, blog_founder, template 
                                            FROM coopera INNER JOIN blog 
                                            ON username = :username AND coopera.blog_id = blog.blog_id");
            $statement->bindValue(':username', $username);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiCoautori($pdo, $blog_id) {
        try {
            $statement = $pdo->prepare("SELECT username FROM coopera WHERE blog_id = :blog_id");
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function inserisciCoautore($pdo, $username, $blog_id) {
        try {
            $statement = $pdo->prepare("INSERT INTO coopera (username, blog_id)
            VALUES (:username, :blog_id)");
            $statement->bindValue(':username', $username);
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function cancellaAutore($pdo, $username, $blog_id) {
        try {
            $statement = $pdo->prepare('DELETE FROM coopera WHERE username = :username AND blog_id = :blog_id');

            $statement->bindValue(':username', $username);
            $statement->bindValue(':blog_id', $blog_id);

            $statement->execute();
           
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    # Query post
    function estraiBlogPost($pdo, $blog_id) {
        try {
            $statement = $pdo->prepare('SELECT * FROM post WHERE blog_id = :blog_id');
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiPostInfo($pdo, $post_id) {
        try {
            $statement = $pdo->prepare('SELECT * FROM post WHERE post_id = :post_id LIMIT 1');
            $statement->bindValue(':post_id', $post_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiCommentiPost($pdo, $post_id) {
        try {
            $statement = $pdo->prepare('SELECT commento_writer, utente.avatar, commento_id, commento_contenuto, commento_data
                                        FROM commento 
                                        INNER JOIN utente ON commento.commento_writer = utente.username
                                        WHERE post_id = :post_id');
            $statement->bindValue(':post_id', $post_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    function inserisciCommento($pdo, $commento, $autore, $post_id) {
        try {
            $statement = $pdo->prepare("INSERT INTO commento (commento_contenuto, commento_data, commento_writer, post_id)
            VALUES (:commento_contenuto, :commento_data, :commento_writer, :post_id)");
            $statement->bindValue(':commento_contenuto', $commento);
            $statement->bindValue(':commento_data', gmdate("Y-m-d H:i:s"));
            $statement->bindValue(':commento_writer', $autore);
            $statement->bindValue(':post_id', $post_id);
            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function aggiornaNumeroCommenti($pdo, $numero, $post_id) {
        try {
            $statement = $pdo->prepare("UPDATE post SET num_commenti = :numero WHERE post_id = :post_id");
            $statement->bindValue(':numero', $numero);
            $statement->bindValue(':post_id', $post_id);
            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    # Funzioni categoria
    function estraiCategoriaBlog($pdo, $blog_id) {
        try {
            $statement = $pdo->prepare("SELECT categoria.categoria_id, categoria_nome, sub_id 
                                        FROM categoria INNER JOIN appartiene 
                                        ON blog_id = :blog_id AND categoria.categoria_id = appartiene.categoria_id");
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiCategorie($pdo) {
        try {
            $statement = $pdo->prepare("SELECT * FROM categoria WHERE parent_id IS NULL");
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiDatiCategoria($pdo, $categoria_id) {
        try {
            $statement = $pdo->prepare("SELECT * FROM categoria WHERE categoria_id = :categoria_id");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiSottoCategorie($pdo, $categoria_id) {
        try {
            $statement = $pdo->prepare("SELECT * FROM categoria WHERE parent_id = :categoria_id");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function associaBlogCategoria($pdo, $categoria_id, $blog_id) {
        try {
            $statement = $pdo->prepare("INSERT INTO appartiene (categoria_id, blog_id)
            VALUES (:categoria_id, :blog_id)");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function associaSottocategoria($pdo, $categoria_id, $blog_id) {
        try {
            $statement = $pdo->prepare("UPDATE appartiene SET sub_id = :categoria_id WHERE blog_id = :blog_id");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function cancellaBlogCategoria($pdo, $categoria_id, $blog_id) {
        try {
            $statement = $pdo->prepare('DELETE FROM appartiene WHERE categoria_id = :categoria_id AND blog_id = :blog_id');

            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->bindValue(':blog_id', $blog_id);

            $statement->execute();
           
        } catch (\Throwable $th) {
            throw $th;
        }   
    }

    function cancellaBlogSottocategoria($pdo, $blog_id) {
        try {
            $statement = $pdo->prepare('UPDATE appartiene SET sub_id = NULL WHERE blog_id = :blog_id');
            $statement->bindValue(':blog_id', $blog_id);
            $statement->execute();
           
        } catch (\Throwable $th) {
            throw $th;
        }   
    }

    function estraiperCategoria($pdo, $categoria_id) {
        try {
            $statement = $pdo->prepare("SELECT b.blog_id, b.blog_titolo, b.blog_descrizione, b.creazione_data
                                        FROM blog as b, appartiene as a, categoria as c
                                        WHERE b.blog_id = a.blog_id AND a.categoria_id = c.categoria_id 
                                        AND c.categoria_id = :categoria_id
                                        ORDER BY b.blog_id ASC");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraisenzaSottocategoria($pdo, $categoria_id) {
        try {
            $statement = $pdo->prepare("SELECT b.blog_id, b.blog_titolo, b.blog_descrizione, creazione_data
                                        FROM appartiene a
                                        INNER JOIN blog b ON a.blog_id = b.blog_id
                                        WHERE a.categoria_id = :categoria_id AND a.sub_id IS NULL");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function estraiconSottocategorie($pdo, $categoria_id, $sub_id) {
        try {
            $statement = $pdo->prepare("SELECT b.blog_id, b.blog_titolo, b.blog_descrizione, creazione_data
                                        FROM appartiene a
                                        INNER JOIN blog b ON a.blog_id = b.blog_id
                                        WHERE a.categoria_id = :categoria_id AND a.sub_id = :sub_id");
            $statement->bindValue(':categoria_id', $categoria_id);
            $statement->bindValue(':sub_id', $sub_id);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    
  
