<?php
session_cache_limiter(false);
session_start();
$_SESSION['culture'] = 'es';

require '../vendor/autoload.php';

$app = new \Slim\Slim(array(
  'limit_rows_query' => 200,
  'value_same_tag' => 1,
  'value_same_parent_tag' => 1
));

$app->get('/cultures', 'getCultures');
$app->get('/tags', 'getTags');
$app->get('/responses/search/filter/:filter/tags/:tags', 'getResponses');
$app->get('/responses/search/filter/:filter/tags', 'noResults');
$app->post('/responses', 'addTemplate');
$app->delete('/responses/id/:id', 'deleteResponse');
$app->post('/report', 'addReport');
$app->run();



function deleteResponse($id) {

  $sql = "DELETE FROM response_template WHERE id = :id";

  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();

    echo '{"success":{"text": "remove successful"}}';
  }
  catch (PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}


/**
 * Response if the request don't have tags
 */
function noResults() {
  echo '[]';
}

function addTemplate()
{
  $request =  \Slim\Slim::getInstance()->request();
  $newResTemplate = json_decode($request->getBody());

  $sql = "INSERT INTO response_template (used) VALUES (:used)";

  $used = 1;
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("used", $used);
    $stmt->execute();
    $template_id = $db->lastInsertId();

    if(!empty($template_id)) {
      $sql = "INSERT INTO response_template_i18n (template_id, culture, region, comment, advice)
              VALUES (:template_id, :culture, :region, :comment, :advice)";

      $newResTemplate->template_id = $template_id;

      createTagsTemplates($newResTemplate->tags, $template_id);

      $stmt = $db->prepare($sql);
      $stmt->bindParam("template_id", $template_id);
      $stmt->bindParam("culture", $newResTemplate->culture);
      $stmt->bindParam("region", $newResTemplate->region);
      $stmt->bindParam("comment", $newResTemplate->comment);
      $stmt->bindParam("advice", $newResTemplate->advice);
      $stmt->execute();

      echo json_encode($newResTemplate);
    }
    $db = null;
  }
  catch (PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function createTagsTemplates($tags, $template_id)
{
  foreach($tags as $tag) {
    $sql = "INSERT INTO tag_x_response_template (tag_id, template_id) VALUES (:tag_id, :template_id)";

    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("tag_id", $tag);
    $stmt->bindParam("template_id", $template_id);
    $stmt->execute();
  }
}

/**
 * Request to save new report
 */
function addReport()
{
  $request =  \Slim\Slim::getInstance()->request();
  $report = json_decode($request->getBody());

  $sql = "INSERT INTO report (template_id, culture, region, comment, advice)
          VALUES (:template_id, :culture, :region, :comment, :advice)";
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("template_id", $report->template_id);
    $stmt->bindParam("culture", $report->culture);
    $stmt->bindParam("region", $report->region);
    $stmt->bindParam("comment", $report->comment);
    $stmt->bindParam("advice", $report->advice);
    $stmt->execute();
    $report->id = $db->lastInsertId();
    $db = null;

    if (!empty($report->id)) incrementReportUsed($report->template_id);

    echo json_encode($report);
  }
  catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function incrementReportUsed($template_id)
{
  $sql = "UPDATE response_template SET used = used + 1 WHERE id=:id";

  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $template_id);
    $stmt->execute();
    $db = null;
  }
  catch(PDOException $e) {
    return $e;
  }
}



/**
 * Request of response templates
 * @param string $filter
 * @param string $tags
 */
function getResponses($filter, $tags)
{
  $culture = $_SESSION['culture'];

  try {
    $sql = '';
    $arrTags = array();
    $strFind = null;
    parseParamsSearch($tags, $arrTags, $strFind);

    if ( empty($strFind) && empty($arrTags)) throw new HttpRequestException('Nothing to search');
    if ( empty($strFind) && count($arrTags)) $sql = getQueryWidthTags($arrTags, $culture);
    if (!empty($strFind) && empty($arrTags)) $sql = getQueryWidthStrFind($strFind, $filter, $culture);
    if (!empty($strFind) && count($arrTags)) $sql = getQueryWidthTagsAndStrFind($arrTags, $strFind, $filter, $culture);

    $db = getConnection();
    $stmt = $db->query($sql);
    $responseTemplates = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    echo json_encode($responseTemplates);
  }
  catch(PDOException $e) {
    echo '[{"error":{"text":'. $e->getMessage() .'}}]';
  }
  catch(HttpRequestException $e) {
    echo '[{"error":{"text":'. $e->getMessage() .'}}]';
  }
}


/**
 * @param string $strTags
 * @param array  $tags
 * @param null   $strFind
 */
function parseParamsSearch($strTags, &$tags, &$strFind)
{
  $arrTags = explode(',', $strTags);

  foreach ($arrTags as $value)
  {
    if (is_numeric($value)) {
      $tags[] = $value;
    } else {
      $strFind = $value;
    }
  }
}


/**
 * @param array  $tags
 * @param string $culture
 *
 * @return string
 */
function getQueryWidthTags($tags, $culture)
{
  $caseTags = '';
  $caseParents = '';
  $parents = getParentTags($tags);
  $slim = \Slim\Slim::getInstance();
  $limit = $slim->config('limit_rows_query');
  $valueTag = $slim->config('value_same_tag');
  $valueParent = $slim->config('value_same_parent_tag');

  $cTags = count($tags);
  foreach ($tags as $tag) {
    $caseTags .= "WHEN $tag THEN $valueTag ";
  }

  foreach ($parents as $parent) {
    $caseParents .= "WHEN ".$parent['parent_id']." THEN $valueParent ";
  }

  $sql = "SELECT e.*, IF(e.count_tags = $cTags AND e.matches = $cTags *2, 1, 0) exactly
        FROM (
          SELECT r.id, rt.culture, rt.region, rt.comment, rt.advice, COUNT(t.tag_id) count_tags, r.used,
          (SUM(CASE t.tag_id $caseTags ELSE 0 END) + SUM(CASE ta.parent_id $caseParents ELSE 0 END)) matches,
          GROUP_CONCAT(ti.name SEPARATOR '||') tags
          FROM response_template_i18n rt
          LEFT JOIN response_template r ON r.id = rt.template_id
          LEFT JOIN tag_x_response_template t ON t.template_id = rt.template_id
          LEFT JOIN tag ta ON ta.id = t.tag_id
          LEFT JOIN tag_i18n ti ON ta.id = ti.tag_id
          WHERE rt.culture = '$culture'
          GROUP BY rt.template_id
          ORDER BY matches DESC, r.used DESC, rt.culture
          LIMIT 200
        ) e
        WHERE matches > 0
        ORDER BY exactly DESC, e.matches DESC, e.used DESC, e.culture
        LIMIT $limit ";

  return $sql;
}

function getParentTags($tags)
{
  $strTags = implode(',', $tags);

  $sql = "SELECT t.parent_id FROM tag t WHERE t.id IN ($strTags)";
  $db = getConnection();
  $stmt = $db->query($sql);
  $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $db = null;

  return $parents;
}

function getQueryWidthStrFind($strFind, $filter, $culture)
{
  $limit = \Slim\Slim::getInstance()->config('limit_rows_query');

  if ($filter == 'all') {
    $caseFinder = "rt.region LIKE '%$strFind%' OR rt.comment LIKE '%$strFind%' OR rt.advice LIKE '%$strFind%'";
  } else {
    $caseFinder = "rt.$filter LIKE '%$strFind%'";
  }

  $sql = "SELECT rt.*, 1 as matches, t.id, t.used, 0 as exactly, GROUP_CONCAT(ti.name) tags
      FROM response_template_i18n rt
      LEFT JOIN response_template t ON t.id = rt.template_id
      LEFT JOIN tag_x_response_template tt ON tt.template_id = t.id
      LEFT JOIN tag_i18n ti ON ti.tag_id = tt.tag_id
      WHERE rt.culture = '$culture'
      AND ($caseFinder)
      GROUP BY rt.template_id
      ORDER BY matches DESC, t.used DESC
      LIMIT $limit ";

  return $sql;
}


function getQueryWidthTagsAndStrFind($tags, $strFind, $filter, $culture)
{
  $caseTags = '';
  $caseParents = '';
  $parents = getParentTags($tags);
  $slim = \Slim\Slim::getInstance();
  $limit = $slim->config('limit_rows_query');
  $valueTag = $slim->config('value_same_tag');
  $valueParent = $slim->config('value_same_parent_tag');

  $cTags = count($tags);
  foreach ($tags as $tag) {
    $caseTags .= "WHEN $tag THEN $valueTag ";
  }

  foreach ($parents as $parent) {
    $caseParents .= "WHEN ".$parent['parent_id']." THEN $valueParent ";
  }

  if ($filter == 'all') {
    $caseFinder = "rt.region LIKE '%$strFind%' OR rt.comment LIKE '%$strFind%' OR rt.advice LIKE '%$strFind%'";
  } else {
    $caseFinder = "rt.$filter LIKE '%$strFind%'";
  }

  $sql = "SELECT e.*, IF(e.count_tags = $cTags AND e.matches = $cTags *2, 1, 0) exactly
        FROM (
          SELECT r.id, rt.culture, rt.region, rt.comment, rt.advice, COUNT(t.tag_id) count_tags, r.used,
          (SUM(CASE t.tag_id $caseTags ELSE 0 END) + SUM(CASE ta.parent_id $caseParents ELSE 0 END)) matches,
          GROUP_CONCAT(ti.name SEPARATOR '||') tags
          FROM response_template_i18n rt
          LEFT JOIN response_template r ON r.id = rt.template_id
          LEFT JOIN tag_x_response_template t ON t.template_id = rt.template_id
          LEFT JOIN tag ta ON ta.id = t.tag_id
          LEFT JOIN tag_i18n ti ON ti.tag_id = ta.id
          WHERE rt.culture = '$culture'
          AND ($caseFinder)
          GROUP BY rt.template_id, rt.culture
          LIMIT 200
        ) e
        WHERE matches > 0
        ORDER BY exactly DESC, e.matches DESC, e.used DESC, e.culture
        LIMIT $limit ";

  return $sql;
}

/**
 * Request of cultures
 */
function getCultures()
{
  $sql = "SELECT c.* FROM culture c ORDER BY c.language";

  try {
    $db = getConnection();
    $stmt = $db->query($sql);
    $cultures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db = null;

    echo json_encode($cultures);
  }
  catch(PDOException $e) {
    echo '{"error":{"text":'.$e->getMessage().'}}';
  }
}


/**
 * Request of tags and tag families
 */
function getTags()
{
  $culture = $_SESSION['culture'];
  $sql = "SELECT t.*, ti.culture, ti.name, f.name AS family
          FROM tag t
          LEFT JOIN tag_i18n ti ON t.id = ti.tag_id
          LEFT JOIN family_tag f ON f.id = t.family_id AND f.culture = '$culture'
          WHERE ti.culture = '$culture'
          AND t.parent_id IS NOT NULL
          ORDER BY t.family_id, ti.name";
  try {
    $db = getConnection();
    $stmt = $db->query($sql);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $families = hydrateArrFamilies($tags);
    $db = null;

    echo json_encode($families);
  }
  catch(PDOException $e) {
    echo '[{"error":{"text":'. $e->getMessage() .'}}]';
  }
}


/**
 * Hydrate the tags values in array, in the first dimension of array is set tags families,
 * in the second dimension is set the full tag value.
 *
 * @param array $tags
 * @return array
 */
function hydrateArrFamilies($tags)
{
  $families = array();
  $tmp = array();

  foreach($tags as $tag) {
    if(empty($families) || !in_array($tag['family_id'], $tmp)) {
      $families[] = array(
          'id' => $tag['family_id'],
          'family' => $tag['family'],
      );
      $tmp[] = $tag['family_id'];
    }
  }

  foreach ($tags as $tag) {
    foreach ($families as &$family) {
      if($tag['family_id'] == $family['id']) {
        $family['tags'][] = $tag;
      }
    }
  }

  return $families;
}

/**
 * Connection to data base.
 * @return PDO
 */
function getConnection()
{
  $dbhost="127.0.0.1";
  $dbuser="root";
  $dbpass="root";
  $dbname="wine";
  $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=UTF8", $dbuser, $dbpass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  return $dbh;
}
