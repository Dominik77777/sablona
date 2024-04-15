<?php

namespace otazkyodpovede;
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/db/config.php'); //root - absolútna cesta k adresáru projektu, používa sa v pripade ze php nechce prečítat spravnu cestu
use PDO;

class QnA{
    private $conn;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()  // spojenie s databazou
    {
        $config = DATABASE;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );
        try {
            $this->conn = new PDO('mysql:host=' . $config['HOST'] . ';dbname=' . $config['DBNAME'] . ';port=' . $config['PORT'], $config['USER_NAME'], $config['PASSWORD'], $options);
        } catch (PDOException $e) {
            die("Chyba pripojenia: " . $e->getMessage());
        }
    }

    public function insertQnA(){
        try {
            // Načítanie JSON súboru
            $data = json_decode(file_get_contents(__ROOT__ . '/data/datas.json'), true);
            if (!isset($data["otazka"]) || !isset($data["odpoved"])) {
                throw new \Exception("Invalid JSON structure");
            }
            $otazka = $data["otazka"];
            $odpoved = $data["odpoved"];
            // Vloženie otázok a odpovedí v rámci transakcie
            $this->conn->beginTransaction();
            $sql = "INSERT INTO qna (otazka, odpoved) VALUES (:otazka, :odpoved)"; //vytvoríme si SQL príkaz s parametrami
            $statement = $this->conn->prepare($sql);
            for ($i = 0; $i < count($otazka); $i++) {     //cyklom prechádzame cez otázky a odpovede a vloží záznam do databázy pomocou bindParam() a execute()
                $statement->bindParam(':otazka', $otazka[$i]);
                $statement->bindParam(':odpoved', $odpoved[$i]);
                $statement->execute();
            }
            $this->conn->commit();   //po vložení všetkých záznamov sa transakcia vloží pomocou commit()
            echo "Dáta boli vložené";
        } catch (\Exception $e) {
            //Zobrazenie chybového hlásenia
            echo "Chyba pri vkladaní dát do databázy: " . $e->getMessage();
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            //Vrátenie späť zmien v prípade chyby
        } finally {
            //Uzatvorenie spojenia s databázou
            $this->conn = null;
        }
    }
    public function getQnA(){
        try {
            // vykonanie SQL dotazu na výber otázok a odpovedí
            $query = $this->conn->query("SELECT * FROM qna");
            $qna = $query->fetchAll(PDO::FETCH_ASSOC);

            // zobrazenie otázok a odpovedí
            foreach ($qna as $item) {
                echo "<p><strong>Otázka:</strong> {$item['otazka']}</p>";
                echo "<p><strong>Odpoveď:</strong> {$item['odpoved']}</p>";
            }
        } catch (\Exception $e) {
            // Zobrazenie správy v prípade chyby
            echo "Chyba pri načítavaní otázok a odpovedí: " . $e->getMessage();
        }
    }
}