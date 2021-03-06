<?php

namespace DAO;

use \Exception as Exception;
use DAO\ICompanyDAO as ICompanyDAO;
use DAO\Connection as Connection;
use DAO\QueryType as QueryType;
use Models\Company as Company;
use Models\Categoty as Category;

class CompanyDAO implements ICompanyDAO
{
    private $connection;

    private $tableName = "Company";

    public function Add(Company $company)
    {
        try {
            $query = "CALL save_Company (:companyName, :phoneNumber, :email, :description, @id);";

            $parameters["companyName"] = $company->getName();
            $parameters["description"] = $company->getDescription();
            $parameters["phoneNumber"] = $company->getPhoneNumber();
            $parameters["email"] = $company->getEmail();
            $parameters["description"] = $company->getDescription();

            $this->connection = Connection::GetInstance();

            $lastId = $this->connection->Execute($query, $parameters);
            
            return $lastId[0]["id"];
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function GetAll()
    {
        try {

            $companyList = array();

            $query = "SELECT * FROM " . $this->tableName . " WHERE active = 1";

            $this->connection = Connection::GetInstance();

            $resultSet = $this->connection->Execute($query);

            foreach ($resultSet as $row) {

                $company = new Company();

                $company->setIdCompany($row["idCompany"]);
                $company->setName($row["companyName"]);
                $company->setPhoneNumber($row["phoneNumber"]);
                $company->setEmail($row["email"]);
                $company->setDescription($row["description"]);
                $company->setState($row["active"]);

                array_push($companyList, $company);
            }

            return $companyList;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function Remove($idCompany)
    {
        try {

            $query = "UPDATE " . $this->tableName . " SET active = FALSE WHERE idCompany = " . $idCompany;

            $this->connection = Connection::GetInstance();

            $this->connection->ExecuteNonQuery($query);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function Edit($company)
    {
        try {

            $query = "CALL update_Company(:idCompany, :companyName, :cuit, :phoneNumber, :email, :description);";

            $parameters["idCompany"] = $company->getIdCompany();
            $parameters["companyName"] = $company->getName();
            $parameters["phoneNumber"] = $company->getPhoneNumber();
            $parameters["email"] = $company->getEmail();
            $parameters["description"] = $company->getDescription();

            $this->connection = Connection::GetInstance();

            $this->connection->ExecuteNonQuery($query, $parameters);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function getId($name)
    {
        $foundCompany = new Company;
        try {

            $query = "SELECT idCompany FROM " . $this->tableName . ' WHERE companyName = :companyName;';

            $parameters["companyName"] = $name;

            $this->connection = Connection::GetInstance();

            $foundCompany  = $this->connection->Execute($query, $parameters);

            $idCompany = $foundCompany[0]["idCompany"];

            return $idCompany;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function searchId($idCompany)
    {
        $foundCompany = new Company;
        try {
            $query = "SELECT * FROM " . $this->tableName . " WHERE `idCompany` = " . $idCompany;

            $this->connection = Connection::GetInstance();

            $foundCompany  = $this->connection->Execute($query);

            foreach ($foundCompany as $row) {
                $company = new Company();

                $company->setIdCompany($row["idCompany"]);
                $company->setName($row["companyName"]);
                $company->setPhoneNumber($row["phoneNumber"]);
                $company->setEmail($row["email"]);
                $company->setDescription($row["description"]);
                $company->setState($row["active"]);
            }

            return $company;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function getCompanyByEmail($email)
    {
        try {
            $query = "SELECT * FROM " . $this->tableName . " WHERE email = :email";

            $parameter['email'] = $email;

            $this->connection = Connection::GetInstance();

            $foundCompany  = $this->connection->Execute($query, $parameter);

            if($foundCompany){
                $company = new Company();

                $company->setIdCompany($foundCompany[0]["idCompany"]);
                $company->setName($foundCompany[0]["companyName"]);
                $company->setPhoneNumber($foundCompany[0]["phoneNumber"]);
                $company->setEmail($foundCompany[0]["email"]);
                $company->setDescription($foundCompany[0]["description"]);
                $company->setState($foundCompany[0]["active"]);
                
                return $company;
            } else {
                return NULL;
            }            
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function filterList($parameters)
    {
        try {

            $companyList = array();

            $query = "SELECT * FROM $this->tableName c JOIN Address a ON c.idCompany = a.idCompany WHERE c.active = 1";

            $filteredList = array_filter($parameters); // removes empty values from $_POST

            if ($filteredList) { // not empty
                $query .= " AND";

                foreach ($filteredList as $key => $value) {
                    $query .= " $key LIKE '%$value%'";  // $filteredList keyname = $filteredList['keyname'] value
                    if (count($filteredList) > 1 && (array_key_last($filteredList) != $key)) { // more than one search filter, and not the last
                        $query .= " AND ";
                    }
                }
            }
            $query .= ";";

            $this->connection = Connection::GetInstance();

            $foundCompany  = $this->connection->Execute($query);

            foreach ($foundCompany as $row) {
                $company = new Company();

                $company->setIdCompany($row["idCompany"]);
                $company->setName($row["companyName"]);
                $company->setPhoneNumber($row["phoneNumber"]);
                $company->setEmail($row["email"]);
                $company->setDescription($row["description"]);
                $company->setState($row["active"]);

                array_push($companyList, $company);
            }

            return $companyList;

        } catch (Exception $ex) 
        {
            throw $ex;
        }
    }
}
