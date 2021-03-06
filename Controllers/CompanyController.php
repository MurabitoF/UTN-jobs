<?php

namespace Controllers;

use Exception;
use Controllers\LoggerController as LoggerController;
use DAO\CompanyDAO as CompanyDAO;
use DAO\CareerDAO as CareerDAO;
use DAO\AddressDAO as AddressDAO;
use Models\Company as Company;
use Models\Address as Address;
use Models\Alert as Alert;
use Models\User as User;

class CompanyController
{
    private $companyDAO;
    private $careerDAO;
    private $addressDAO;

    public function __construct()
    {
        $this->companyDAO = new CompanyDAO;
        $this->careerDAO = new CareerDAO;
        $this->addressDAO = new AddressDAO;
    }


    public function ShowAddView($alert = NULL, $email = NULL)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        if (in_array('Create Company', LoggerController::$permissions[$_SESSION['loggedUser']->getRole()])) {
            $careerList = $this->careerDAO->GetAll();
            require_once(VIEWS_PATH . "company-add.php");
        } else {
            echo "<script> alert('No tenes permisos para entrar a esta pagina'); </script>";
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        }
    }

    public function ShowEditView($idCompany, $alert = NULL)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        if (in_array('Edit Company', LoggerController::$permissions[$_SESSION['loggedUser']->getRole()])) {
            $company = $this->companyDAO->searchId($idCompany);

            $address = $this->addressDAO->getAddresses($idCompany);

            require_once(VIEWS_PATH . "company-edit.php");
        } else {
            echo "<script> alert('No tenes permisos para entrar a esta pagina'); </script>";
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        }
    }

    public function VerifyData()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        $company = $this->companyDAO->getCompanyByEmail($_SESSION['loggedUser']->getUsername());
        if($company){
            $company->setIdUser($_SESSION["loggedUser"]->getIdUser());
            $company->setUsername($_SESSION["loggedUser"]->getUsername());
            $company->setPassword($_SESSION["loggedUser"]->getPassword());
            $company->setRole($_SESSION["loggedUser"]->getRole());
            $company->setActive($_SESSION["loggedUser"]->getActive());
            $_SESSION["loggedUser"] = $company;
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        } else {
            $this->ShowAddView(NULL, $_SESSION['loggedUser']->getUsername());
        }
    }

    public function ShowDataView($idCompany)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();

        $company = $this->companyDAO->searchId($idCompany);

        $address = $this->addressDAO->getAddresses($idCompany);

        require_once(VIEWS_PATH . "company-data.php");
    }


    public function ShowListView($name = null, $city = null, $alert = null)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();

        $filterParameters["companyName"] = $name;
        $filterParameters["city"] = $city;

        $companyList = $this->companyDAO->filterList($filterParameters);

        $addressList = $this->addressDAO->GetAll();


        require_once(VIEWS_PATH . "company-list.php");
    }

    public function Add($name, $phoneNumber, $email, $city, $postalCode, $stateName, $description, $streetName, $streetAddress)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        if (in_array('Create Company', LoggerController::$permissions[$_SESSION['loggedUser']->getRole()])) {
            try {
                $company = new Company();
                $company->setName($name);
                $company->setPhoneNumber($phoneNumber);
                $company->setEmail($email);
                $company->setDescription($description);

                $address = new Address();
                $address->setCity($city);
                $address->setPostalCode($postalCode);
                $address->setStateName($stateName);
                $address->setStreetName($streetName);
                $address->setStreetAddress($streetAddress);

                $this->companyDAO->Add($company);

                $idCompany = $this->companyDAO->getId($name);

                $this->addressDAO->Add($address, $idCompany);

                $alert = new Alert("success", "La empresa a sido ingresada con exito");
            } catch (Exception $ex) {
                if (str_contains($ex->getMessage(), 1062)) {
                    $alert = new Alert("warning", "La empresa ingresada ya existe");
                } else {
                    $alert = new Alert("danger", $ex->getMessage());
                }
            } finally {
                if ($_SESSION['loggedUser']->getRole() == "Company") {
                    $this->VerifyData();
                } else {
                    $this->ShowAddView($alert);
                }
            }
        } else {
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        }
    }

    public function Edit($idCompany, $name, $phoneNumber, $email, $city, $postalCode, $stateName, $description, $streetName, $streetAddress)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        if (in_array('Edit Company', LoggerController::$permissions[$_SESSION['loggedUser']->getRole()])) {
            try {
                $company = $this->companyDAO->searchId($idCompany);

                $address = $this->addressDAO->getAddresses($idCompany);

                $company->setName($name);
                $company->setPhoneNumber($phoneNumber);
                $company->setEmail($email);
                $company->setDescription($description);

                $this->companyDAO->Edit($company);

                $address->setCity($city);
                $address->setPostalCode($postalCode);
                $address->setStateName($stateName);
                $address->setStreetName($streetName);
                $address->setStreetAddress($streetAddress);

                $this->addressDAO->Edit($address);

                $alert = new Alert('success', 'La empresa fue editada correctamente');
                $this->ShowListView("", "", $alert);
            } catch (Exception $ex) {
                $alert = new Alert('danger', $ex->getMessage());
                $this->ShowEditView($idCompany, $alert);
            }
        } else {
            echo "<script> alert('No tenes permisos para entrar a esta pagina'); </script>";
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        }
    }

    public function Remove($idCompany)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        LoggerController::VerifyLogIn();
        if (in_array('Delete Company', LoggerController::$permissions[$_SESSION['loggedUser']->getRole()])) {
            try {

                $this->companyDAO->Remove($idCompany);
                $this->addressDAO->Remove($idCompany);

                $alert = new Alert("success", "La empresa fue dada de baja con exito.");
            } catch (Exception $ex) {
                $alert = new Alert("danger", "Error: " . $ex->getMessage());
            } finally {
                $this->ShowListView();
            }
        } else {
            echo "<script> alert('No tenes permisos para entrar a esta pagina'); </script>";
            header("Location: " . FRONT_ROOT . "User/ShowHomeView");
        }
    }
}
