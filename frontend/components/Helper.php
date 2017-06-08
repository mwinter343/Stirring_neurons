<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 4/10/15
 * Time: 4:11 PM
 */

namespace backend\components;

use yii\helpers\Html;
class Helper {
    /**
     *
     * Helper method to return a datetime object in a more read friendly format
     * @param $date - the date to be modified
     * @param string $placeholder - the string to be returned in the event the date object is null
     * @return bool|string
     */
    public static function readableDate($date, $placeholder = 'Null') {
        if (!is_null($date)) {

            //checks if date given, or date time is given
            if (strlen($date) > 10) {
                $date = strtotime($date);
                $readableDate = date("F j, Y, g:i a", $date);
            } else {
                $date = strtotime($date);
                $readableDate = date("F j, Y", $date);
            }
        } else {
            $readableDate = $placeholder;
        }

        return $readableDate;
    }

    /**
     *
     * Helper method to return a datetime object in a more read friendly format
     * @param $date - the date to be modified
     * @param string $placeholder - the string to be returned in the event the date object is null
     * @return bool|string
     */
    public static function readableDateTruncateTime($date, $placeholder = 'Null') {
        if (!is_null($date)) {
            $date = strtotime($date);
            $readableDate = date("F j, Y", $date);
        } else {
            $readableDate = $placeholder;
        }

        return $readableDate;
    }

    /**
     *
     * Calculates the average of 2 numbers, rounding off to the decimal point specified via $precision
     * and in the case that $denominator is 0, returns $caseZero instead
     * @param $numerator
     * @param $denominator
     * @param $precision
     * @param int $caseZero
     * @return float|int
     */
    public static function calcAverage($numerator, $denominator, $precision, $caseZero = 0) {
        //prevent divide by 0
        if ($denominator > 0) {
            return round($numerator / $denominator, $precision);
        } else {
            return $caseZero;
        }

    }

    /**
     * Checks if the object is null (or an empty string) and replaces it with $placeholder if it is
     * @param $obj
     * @param $placeholder
     * @return mixed
     */
    public static function replaceIfNull($obj, $placeholder) {
        if (is_null($obj) || $obj == '') {
            return $obj = $placeholder;
        } else {
            return $obj;
        }
    }

    /**
     * returns the equivalent of mysql's version of now
     * @return string
     */
    public static function now() {
        $timestamp = time();
        $dt = new \DateTime("now", new \DateTimeZone('Africa/Accra')); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        return $dt->format('Y-m-d H:i');
    }

    /**
     * Checks if the date given is a valid mysql date
     * @param $date
     * @return bool
     */
    public static function validateMysqlDate($date) {
        return (bool)strtotime($date);
    }

    /**
     * Checks if $_GET variable is set and not an empty string
     * @param $get - the get variable to be checked
     * @return the Html::encoded get variable or null if not set or empty
     */
    public static function checkGET($get) {
        if (isset($_GET[$get]) && !$_GET[$get] == "") {
            return Html::encode($_GET[$get]);
        } else {
            return null;
        }

    }

    /**
     * Checks the suffix of a subscription string, and converts it to more user friendly terms if need be.
     * @param $subscription - the subscription string to be checked
     *
     * @return bool|string
     */
    public static function checkSubscriptionSuffix($subscription) {
        if (!(substr($subscription, -1) == '1' || substr($subscription, -2) == '1y')) { //remove "bas" once basic has been removed from db
            return ucfirst($subscription);
        } else if (substr($subscription, -1) == '1') {
            return substr(ucfirst($subscription), 0, -1) . " (Unverified)";
        } else if (substr($subscription, -2) == '1y') {
            return substr(ucfirst($subscription), 0, -2) . "  (Unverified Annual)";
        }

        return false;
    }

    /**
     * @param $activity - the activity being translated
     * @return string
     */
    public static function activityAttributeLabels($activity) {
        switch ($activity) {
            case 'Rollup':
                $activity = 'Calculate';
                break;
            case 'Two By Two':
                $activity = 'Quadrant';
                break;
            case 'Item':
                $activity = 'Slide';
                break;
        }

        return $activity;
    }

    public static function yesterday() {
        $timestamp = time() - 3600 * 24; //day ago
        $dt = new \DateTime("now", new \DateTimeZone('Africa/Accra')); //timezone of mysql servers
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        return $dt->format('Y-m-d H:i');
    }

    /**
     *
     * Creates bootstrap (table-striped, table hover) with hyperlinks to view file specified
     * @param $array
     * @param $columnTitles
     * @param $columns
     * @param $viewFile
     * @param null $from
     * @param null $to
     * @param string $placeholder
     */
    public static function createdTableHyperLinks($array, $columnTitles, $columns, $viewFile, $from, $to, $header = null, $placeholder = 'n/a') {


        if(is_null($array) || !$array){
            return false;
        }
        echo '<table class="modal_table table table-striped table-hover">';

        echo "<thead>";
        if ($header) {
            echo '<tr>';
            echo '<th class="bg-primary" colspan=' . count($columns) . '.>';
            echo '<h4>' . $header . '</h4>';
            echo '</th></tr>';
        }

        //row with column names at the head of the table
        echo "<tr>";
        foreach ($columnTitles as $headers) {

            echo "<th>$headers</th>";
        }
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        foreach ($array as $value) {
            //begin row
            echo "<tr>";

            //insert data for each column
            foreach ($columns as $c) {
                if (substr($c, 0, 6) == 'events') {
                    echo "<td>";
                    if (array_key_exists(0, $value['events'])) {
                        //echo $value['events'][0]['' . substr($c, 7, -1)]; //the idevent of their last meeting
                        echo count($value['events']);
                        echo "</td>";
                    } else {
                        echo "0";
                        echo "</td>";
                    }
                }
            }

            //end row
            echo "</tr>";
        }
        echo "</tbody>";
        echo '</table>';
    }

    /**
     * Creates a bootstrap hyperlink to a user profiles
     * @param $value - the value being displayed to user
     * @param $key - the email of the person you want to link to
     * @param $viewFile - the view file to render to
     * @param null $from
     * @param null $to
     */
    public static function userLink($value, $key, $viewFile, $from = null, $to = null) {
        echo Html::a($value, [$viewFile,
            'e' => $key, 'from' => $from, 'to' => $to], ['class' => 'profile-link']);
    }

    /**
     * Creates a bootstrap hyperlink to a subscription
     * @param $value - the value being displayed to user
     * @param $key - the subscription number you want to link to
     * @param $viewFile - the view file to render to
     * @param null $from
     * @param null $to
     */
    public static function subscriptionLink($value, $key, $viewFile, $from = null, $to = null) {
        if (is_numeric($key)) {
            echo Html::a($value, [$viewFile,
                'idsubscription' => $key, 'from' => $from, 'to' => $to], ['class' => 'profile-link']);
        } else {
            echo $value;
        }
    }

    /**
     * Generates 4 arrays: 1 containing all user names, 1 containing all emails, 1 containing all companies
     * and 1 containing all names, emails, and companies.
     *
     * @returns an array with all 4 arrays.
     */
    public static function getNamesEmailsCompanies() {
        $user = new \backend\models\UserSuccessDashboard();
        //generate list of names
        $userList = $user->getAllUserNames();
        //generate list of emails
        $emailList = $user->getAllEmails();
        //generate list of companies
        $companyList = $user->getAllCompanies();

        //combine
        $all = array();
        $all = \yii\helpers\ArrayHelper::merge($all, $userList);
        $all = \yii\helpers\ArrayHelper::merge($all, $companyList);
        $all = \yii\helpers\ArrayHelper::merge($all, $emailList);

        //get rid of duplicates
        $typeAheadArray['all'] = array_unique($all, SORT_REGULAR);

        return array('userList' => $userList, 'emailList' => $emailList, 'companyList' => $companyList, 'all' => $all);
    }
}