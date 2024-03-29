<?php
/**
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     MarketplaceWebService
 *  @copyright   Copyright 2009 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2009-01-01
 */
/*******************************************************************************
 *
 *  Marketplace Web Service PHP5 Library
 *  Generated: Thu May 07 13:07:36 PDT 2009
 *
 *  Modified $Id$
 */

define('CONVERTED_PARAMETERS_KEY', 'PARAMETERS');
define('CONVERTED_HEADERS_KEY', 'HEADERS');

/**
 * The Amazon Marketplace Web Service contain APIs for inventory and order management.
 *
 * MarketplaceWebService_Client is an implementation of MarketplaceWebService
 *
 * @todo improve backslash escapes by not using preg for single-char replacements
 * @todo get rid of static includes
 * @todo wrap request into single __call() statement
 * @todo use marketplace id list
 * @todo determine purpose of convert* and try to simplify redundancy with
 * request classes.
 */
class MarketplaceWebService_Client
implements MarketplaceWebService_Interface
{

    /** @var string */
    private  $awsAccessKeyId = null;

    /** @var string */
    private  $awsSecretAccessKey = null;

    /** @var array */
    private  $config = array ('ServiceURL' => null,
                            'UserAgent' => 'PHP Client Library/2011-08-01 (Language=PHP5)',
                            'SignatureVersion' => 2,
                            'SignatureMethod' => 'HmacSHA256',
                            'ProxyHost' => null,
                            'ProxyPort' => -1,
                            'MaxErrorRetry' => 3,
    );

    const SERVICE_VERSION = '2009-01-01';

    const REQUEST_TYPE = "POST";

    const MWS_CLIENT_VERSION = '2011-08-01';

    const MWS_FORK_VERSION = '2011-08-24';

    private $defaultHeaders = array();

    private $responseBodyContents;

    // "streaming" responses that are errors will be send to this handle;
    private $errorResponseBody;

    private $headerContents;

    private $curlClient;

    /**
     * Construct new Client
     *
     * @param string $awsAccessKeyId AWS Access Key ID
     * @param string $awsSecretAccessKey AWS Secret Access Key
     * @param array $config configuration options.
     * @param string $applicationName application name.
     * @param string $applicationVersion application version.
     * @param array $attributes user-agent attributes
     * @return unknown_type
     * Valid configuration options are:
     * <ul>
     * <li>ServiceURL</li>
     * <li>SignatureVersion</li>
     * <li>TimesRetryOnError</li>
     * <li>ProxyHost</li>
     * <li>ProxyPort</li>
     * <li>MaxErrorRetry</li>
     * </ul>
     */
    public function __construct(
    $awsAccessKeyId, $awsSecretAccessKey, $config, $applicationName, $applicationVersion, $attributes = null) {
        // TODO do not rewrite global iconv charsets by default!
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->awsAccessKeyId = $awsAccessKeyId;
        $this->awsSecretAccessKey = $awsSecretAccessKey;
        if (null !== $config) {
            $this->config = array_merge($this->config, $config);
        }
         
        $this->setUserAgentHeader($applicationName, $applicationVersion, $attributes);
    }

    /**
     * Sets a MWS compliant HTTP User-Agent Header value.
     * $attributeNameValuePairs is an associative array.
     *
     * @param $applicationName
     * @param $applicationVersion
     * @param $attributes
     * @return unknown_type
     */
    public function setUserAgentHeader($applicationName, $applicationVersion, $attributes = null)
    {

        if ( null === $attributes ) {
            $attributes = array ();
        }

        $this->config['UserAgent'] =
        $this->constructUserAgentHeader($applicationName, $applicationVersion, $attributes);
        
        return $this;
    }

    /**
     * Construct a valid MWS compliant HTTP User-Agent Header. From the MWS Developer's Guide, this
     * entails:
     * "To meet the requirements, begin with the name of your application, followed by a forward
     * slash, followed by the version of the application, followed by a space, an opening
     * parenthesis, the Language name value pair, and a closing paranthesis. The Language parameter
     * is a required attribute, but you can add additional attributes separated by semi-colons."
     *
     * @param $applicationName
     * @param $applicationVersion
     * @param $additionalNameValuePairs
     * @return unknown_type
     */
    public function constructUserAgentHeader($applicationName, $applicationVersion, $attributes = null)
    {

        if (null === $applicationName || $applicationName === "") {
            throw new InvalidArguementException('$applicationName cannot be null.');
        }
         
        if (null === $applicationVersion || $applicationVersion === "") {
            throw new InvalidArguementException('$applicationVersion cannot be null.');
        }
         
        $userAgent =
        $this->quoteApplicationName($applicationName)
        . '/'
        . $this->quoteApplicationVersion($applicationVersion);

        $userAgent .= ' (';

        $userAgent .= 'Language=PHP/' . phpversion();
        $userAgent .= '; ';
        $userAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
        $userAgent .= '; ';
        $userAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;

        foreach ($attributes as $key => $value) {
            if (null === $value || $value === '') {
                throw new InvalidArgumentException("Value for $key cannot be null or empty.");
            }

            $userAgent .= '; '
            . $this->quoteAttributeName($key)
            . '='
            . $this->quoteAttributeValue($value);
        }
        $userAgent .= ')';

        return $userAgent;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' character.
     * @todo add unicode support (mind unicode bug in some preg versions)
     * @param $s
     * @return string
     */
    public function collapseWhitespace($s)
    {
        // TODO not unicode safe. But mind the utf bug for older pcre version.
        return preg_replace('/ {2,}|\s/', ' ', $s);
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '/' characters from a string.
     * @param $s
     * @return string
     */
    private function quoteApplicationName($s)
    {
        $quotedString = $this->collapseWhitespace($s);
        // TODO use str_replace
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\//', '\\/', $quotedString);

        return $quotedString;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '(' characters from a string.
     *
     * @param $s
     * @return string
     */
    private function quoteApplicationVersion($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\(/', '\\(', $quotedString);

        return $quotedString;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '=' characters from a string.
     *
     * @param $s
     * @return unknown_type
     */
    private function quoteAttributeName($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\=/', '\\=', $quotedString);

        return $quotedString;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape ';', '\',
     * and ')' characters from a string.
     *
     * @param $s
     * @return unknown_type
     */
    private function quoteAttributeValue($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\;/', '\\;', $quotedString);
        $quotedString = preg_replace('/\\)/', '\\)', $quotedString);

        return $quotedString;
    }

    // Public API ------------------------------------------------------------//

    /**
     * Get Report
     * The GetReport operation returns the contents of a report. Reports can potentially be
     * very large (>100MB) which is why we only return one report at a time, and in a
     * streaming fashion.
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReport.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportRequest request
     * or MarketplaceWebService_Model_GetReportRequest object itself
     * @see MarketplaceWebService_Model_GetReport
     * @return MarketplaceWebService_Model_GetReportResponse MarketplaceWebService_Model_GetReportResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReport($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportRequest) {
            $request = new MarketplaceWebService_Model_GetReportRequest($request);
        }
        return MarketplaceWebService_Model_GetReportResponse::fromXML(
        $this->invoke($this->convertGetReport($request), $request->getReport()));
    }

    /**
     *
     * @param $method
     * @param $args
     */
    public function __call($method, $args) {
        // TODO make namespace a property; maybe even allow multiple namespaces
        $classname = 'MarketplaceWebService_Model_'.ucfirst($method);
        $requestClassname = $classname . 'Request';
        // replacement for service access points
        if (class_exists($requestClassname)) {
            // TODO check function argument count
            $request = array_shift($args);
            if (!$request instanceof $requestClassname) {
                $request = new $requestClassname($request);
            }

            $converted = $this->convert($method, $request);
            // TODO abstract feature to only use if needed
            $dataHandle = $request->getDataHandle();
            $contentMd5 = $request->getContentMd5();
            $xml = $this->invoke($converted, $dataHandle, $contentMd5);

            // using factory
            $ns = ucfirst($method).'Response';
            $response = MarketplaceWebService_ModelResponse::fromXML($xml, $ns);
            return $response;
        }

        // unknown/ unsupported method
        throw new BadMethodCallException("No such method $method.");
    }

    /**
     * Get Report Schedule Count
     * returns the number of report schedules
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportScheduleCount.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportScheduleCountRequest request
     * or MarketplaceWebService_Model_GetReportScheduleCountRequest object itself
     * @see MarketplaceWebService_Model_GetReportScheduleCount
     * @return MarketplaceWebService_Model_GetReportScheduleCountResponse MarketplaceWebService_Model_GetReportScheduleCountResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportScheduleCount($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportScheduleCountRequest) {
            $request = new MarketplaceWebService_Model_GetReportScheduleCountRequest($request);
        }
        return MarketplaceWebService_Model_GetReportScheduleCountResponse::fromXML($this->invoke($this->convertGetReportScheduleCount($request)));
    }

    /**
     * Get Report Request List By Next Token
     * retrieve the next batch of list items and if there are more items to retrieve
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportRequestListByNextToken.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest request
     * or MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest object itself
     * @see MarketplaceWebService_Model_GetReportRequestListByNextToken
     * @return MarketplaceWebService_Model_GetReportRequestListByNextTokenResponse MarketplaceWebService_Model_GetReportRequestListByNextTokenResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportRequestListByNextToken($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest) {
            $request = new MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest($request);
        }
        return MarketplaceWebService_Model_GetReportRequestListByNextTokenResponse::fromXML($this->invoke($this->convertGetReportRequestListByNextToken($request)));
    }

    /**
     * Update Report Acknowledgements
     * The UpdateReportAcknowledgements operation updates the acknowledged status of one or more reports.
     *
     * @see http://docs.amazonwebservices.com/${docPath}UpdateReportAcknowledgements.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_UpdateReportAcknowledgementsRequest request
     * or MarketplaceWebService_Model_UpdateReportAcknowledgementsRequest object itself
     * @see MarketplaceWebService_Model_UpdateReportAcknowledgements
     * @return MarketplaceWebService_Model_UpdateReportAcknowledgementsResponse MarketplaceWebService_Model_UpdateReportAcknowledgementsResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function updateReportAcknowledgements($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_UpdateReportAcknowledgementsRequest) {
            $request = new MarketplaceWebService_Model_UpdateReportAcknowledgementsRequest($request);
        }
        return MarketplaceWebService_Model_UpdateReportAcknowledgementsResponse::fromXML($this->invoke($this->convertUpdateReportAcknowledgements($request)));
    }

    /**
     * Submit Feed
     * Uploads a file for processing together with the necessary
     * metadata to process the file, such as which type of feed it is.
     * PurgeAndReplace if true means that your existing e.g. inventory is
     * wiped out and replace with the contents of this feed - use with
     * caution (the default is false).
     *
     * @see http://docs.amazonwebservices.com/${docPath}SubmitFeed.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_SubmitFeedRequest request
     * or MarketplaceWebService_Model_SubmitFeedRequest object itself
     * @see MarketplaceWebService_Model_SubmitFeed
     * @return MarketplaceWebService_Model_SubmitFeedResponse MarketplaceWebService_Model_SubmitFeedResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function submitFeed($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_SubmitFeedRequest) {
            $request = new MarketplaceWebService_Model_SubmitFeedRequest($request);
        }
        return MarketplaceWebService_Model_SubmitFeedResponse::fromXML(
        $this->invoke($this->convertSubmitFeed($request), $request->getFeedContent(), $request->getContentMd5()));
    }

    /**
     * Get Report Count
     * returns a count of reports matching your criteria;
     * by default, the number of reports generated in the last 90 days,
     * regardless of acknowledgement status
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportCount.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportCountRequest request
     * or MarketplaceWebService_Model_GetReportCountRequest object itself
     * @see MarketplaceWebService_Model_GetReportCount
     * @return MarketplaceWebService_Model_GetReportCountResponse MarketplaceWebService_Model_GetReportCountResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportCount($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportCountRequest) {
            $request = new MarketplaceWebService_Model_GetReportCountRequest($request);
        }
        return MarketplaceWebService_Model_GetReportCountResponse::fromXML($this->invoke($this->convertGetReportCount($request)));
    }

    /**
     * Get Feed Submission List By Next Token
     * retrieve the next batch of list items and if there are more items to retrieve
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetFeedSubmissionListByNextToken.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenRequest request
     * or MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenRequest object itself
     * @see MarketplaceWebService_Model_GetFeedSubmissionListByNextToken
     * @return MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenResponse MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getFeedSubmissionListByNextToken($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenRequest) {
            $request = new MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenRequest($request);
        }
        return MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenResponse::fromXML($this->invoke($this->convertGetFeedSubmissionListByNextToken($request)));
    }

    /**
     * Cancel Feed Submissions
     * cancels feed submissions - by default all of the submissions of the
     * last 30 days that have not started processing
     *
     * @see http://docs.amazonwebservices.com/${docPath}CancelFeedSubmissions.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_CancelFeedSubmissionsRequest request
     * or MarketplaceWebService_Model_CancelFeedSubmissionsRequest object itself
     * @see MarketplaceWebService_Model_CancelFeedSubmissions
     * @return MarketplaceWebService_Model_CancelFeedSubmissionsResponse MarketplaceWebService_Model_CancelFeedSubmissionsResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function cancelFeedSubmissions($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_CancelFeedSubmissionsRequest) {
            $request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest($request);
        }
        return MarketplaceWebService_Model_CancelFeedSubmissionsResponse::fromXML($this->invoke($this->convertCancelFeedSubmissions($request)));
    }

    /**
     * Get Feed Submission Count
     * returns the number of feeds matching all of the specified criteria
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetFeedSubmissionCount.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetFeedSubmissionCountRequest request
     * or MarketplaceWebService_Model_GetFeedSubmissionCountRequest object itself
     * @see MarketplaceWebService_Model_GetFeedSubmissionCount
     * @return MarketplaceWebService_Model_GetFeedSubmissionCountResponse MarketplaceWebService_Model_GetFeedSubmissionCountResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getFeedSubmissionCount($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetFeedSubmissionCountRequest) {
            $request = new MarketplaceWebService_Model_GetFeedSubmissionCountRequest($request);
        }
        return MarketplaceWebService_Model_GetFeedSubmissionCountResponse::fromXML($this->invoke($this->convertGetFeedSubmissionCount($request)));
    }

    /**
     * Cancel Report Requests
     * cancels report requests that have not yet started processing,
     * by default all those within the last 90 days
     *
     * @see http://docs.amazonwebservices.com/${docPath}CancelReportRequests.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_CancelReportRequestsRequest request
     * or MarketplaceWebService_Model_CancelReportRequestsRequest object itself
     * @see MarketplaceWebService_Model_CancelReportRequests
     * @return MarketplaceWebService_Model_CancelReportRequestsResponse MarketplaceWebService_Model_CancelReportRequestsResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function cancelReportRequests($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_CancelReportRequestsRequest) {
            $request = new MarketplaceWebService_Model_CancelReportRequestsRequest($request);
        }
        return MarketplaceWebService_Model_CancelReportRequestsResponse::fromXML($this->invoke($this->convertCancelReportRequests($request)));
    }

    /**
     * Get Report List
     * returns a list of reports; by default the most recent ten reports,
     * regardless of their acknowledgement status
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportList.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportListRequest request
     * or MarketplaceWebService_Model_GetReportListRequest object itself
     * @see MarketplaceWebService_Model_GetReportList
     * @return MarketplaceWebService_Model_GetReportListResponse MarketplaceWebService_Model_GetReportListResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportList($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportListRequest) {
            $request = new MarketplaceWebService_Model_GetReportListRequest($request);
        }
        return MarketplaceWebService_Model_GetReportListResponse::fromXML($this->invoke($this->convertGetReportList($request)));
    }

    /**
     * Get Feed Submission Result
     * retrieves the feed processing report
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetFeedSubmissionResult.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetFeedSubmissionResultRequest request
     * or MarketplaceWebService_Model_GetFeedSubmissionResultRequest object itself
     * @see MarketplaceWebService_Model_GetFeedSubmissionResult
     * @return MarketplaceWebService_Model_GetFeedSubmissionResultResponse MarketplaceWebService_Model_GetFeedSubmissionResultResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getFeedSubmissionResult($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetFeedSubmissionResultRequest) {
            $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($request);
        }
        return MarketplaceWebService_Model_GetFeedSubmissionResultResponse::fromXML(
        $this->invoke($this->convertGetFeedSubmissionResult($request), $request->getFeedSubmissionResult()));
    }

    /**
     * Get Feed Submission List
     * returns a list of feed submission identifiers and their associated metadata
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetFeedSubmissionList.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetFeedSubmissionListRequest request
     * or MarketplaceWebService_Model_GetFeedSubmissionListRequest object itself
     * @see MarketplaceWebService_Model_GetFeedSubmissionList
     * @return MarketplaceWebService_Model_GetFeedSubmissionListResponse MarketplaceWebService_Model_GetFeedSubmissionListResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getFeedSubmissionList($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetFeedSubmissionListRequest) {
            require_once ('MarketplaceWebService/Model/GetFeedSubmissionListRequest.php');
            $request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetFeedSubmissionListResponse.php');
        return MarketplaceWebService_Model_GetFeedSubmissionListResponse::fromXML($this->invoke($this->convertGetFeedSubmissionList($request)));
    }

    /**
     * Get Report Request List
     * returns a list of report requests ids and their associated metadata
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportRequestList.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportRequestListRequest request
     * or MarketplaceWebService_Model_GetReportRequestListRequest object itself
     * @see MarketplaceWebService_Model_GetReportRequestList
     * @return MarketplaceWebService_Model_GetReportRequestListResponse MarketplaceWebService_Model_GetReportRequestListResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportRequestList($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportRequestListRequest) {
            require_once ('MarketplaceWebService/Model/GetReportRequestListRequest.php');
            $request = new MarketplaceWebService_Model_GetReportRequestListRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetReportRequestListResponse.php');
        return MarketplaceWebService_Model_GetReportRequestListResponse::fromXML($this->invoke($this->convertGetReportRequestList($request)));
    }

    /**
     * Get Report Schedule List By Next Token
     * retrieve the next batch of list items and if there are more items to retrieve
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportScheduleListByNextToken.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportScheduleListByNextTokenRequest request
     * or MarketplaceWebService_Model_GetReportScheduleListByNextTokenRequest object itself
     * @see MarketplaceWebService_Model_GetReportScheduleListByNextToken
     * @return MarketplaceWebService_Model_GetReportScheduleListByNextTokenResponse MarketplaceWebService_Model_GetReportScheduleListByNextTokenResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportScheduleListByNextToken($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportScheduleListByNextTokenRequest) {
            require_once ('MarketplaceWebService/Model/GetReportScheduleListByNextTokenRequest.php');
            $request = new MarketplaceWebService_Model_GetReportScheduleListByNextTokenRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetReportScheduleListByNextTokenResponse.php');
        return MarketplaceWebService_Model_GetReportScheduleListByNextTokenResponse::fromXML($this->invoke($this->convertGetReportScheduleListByNextToken($request)));
    }

    /**
     * Get Report List By Next Token
     * retrieve the next batch of list items and if there are more items to retrieve
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportListByNextToken.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportListByNextTokenRequest request
     * or MarketplaceWebService_Model_GetReportListByNextTokenRequest object itself
     * @see MarketplaceWebService_Model_GetReportListByNextToken
     * @return MarketplaceWebService_Model_GetReportListByNextTokenResponse MarketplaceWebService_Model_GetReportListByNextTokenResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportListByNextToken($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportListByNextTokenRequest) {
            require_once ('MarketplaceWebService/Model/GetReportListByNextTokenRequest.php');
            $request = new MarketplaceWebService_Model_GetReportListByNextTokenRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetReportListByNextTokenResponse.php');
        return MarketplaceWebService_Model_GetReportListByNextTokenResponse::fromXML($this->invoke($this->convertGetReportListByNextToken($request)));
    }

    /**
     * Manage Report Schedule
     * Creates, updates, or deletes a report schedule
     * for a given report type, such as order reports in particular.
     *
     * @see http://docs.amazonwebservices.com/${docPath}ManageReportSchedule.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_ManageReportScheduleRequest request
     * or MarketplaceWebService_Model_ManageReportScheduleRequest object itself
     * @see MarketplaceWebService_Model_ManageReportSchedule
     * @return MarketplaceWebService_Model_ManageReportScheduleResponse MarketplaceWebService_Model_ManageReportScheduleResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function manageReportSchedule($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_ManageReportScheduleRequest) {
            require_once ('MarketplaceWebService/Model/ManageReportScheduleRequest.php');
            $request = new MarketplaceWebService_Model_ManageReportScheduleRequest($request);
        }
        require_once ('MarketplaceWebService/Model/ManageReportScheduleResponse.php');
        return MarketplaceWebService_Model_ManageReportScheduleResponse::fromXML($this->invoke($this->convertManageReportSchedule($request)));
    }

    /**
     * Get Report Request Count
     * returns a count of report requests; by default all the report
     * requests in the last 90 days
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportRequestCount.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportRequestCountRequest request
     * or MarketplaceWebService_Model_GetReportRequestCountRequest object itself
     * @see MarketplaceWebService_Model_GetReportRequestCount
     * @return MarketplaceWebService_Model_GetReportRequestCountResponse MarketplaceWebService_Model_GetReportRequestCountResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportRequestCount($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportRequestCountRequest) {
            require_once ('MarketplaceWebService/Model/GetReportRequestCountRequest.php');
            $request = new MarketplaceWebService_Model_GetReportRequestCountRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetReportRequestCountResponse.php');
        return MarketplaceWebService_Model_GetReportRequestCountResponse::fromXML($this->invoke($this->convertGetReportRequestCount($request)));
    }

    /**
     * Get Report Schedule List
     * returns the list of report schedules
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetReportScheduleList.html
     * @param mixed $request array of parameters for MarketplaceWebService_Model_GetReportScheduleListRequest request
     * or MarketplaceWebService_Model_GetReportScheduleListRequest object itself
     * @see MarketplaceWebService_Model_GetReportScheduleList
     * @return MarketplaceWebService_Model_GetReportScheduleListResponse MarketplaceWebService_Model_GetReportScheduleListResponse
     *
     * @throws MarketplaceWebService_Exception
     */
    public function getReportScheduleList($request)
    {
        if (!$request instanceof MarketplaceWebService_Model_GetReportScheduleListRequest) {
            require_once ('MarketplaceWebService/Model/GetReportScheduleListRequest.php');
            $request = new MarketplaceWebService_Model_GetReportScheduleListRequest($request);
        }
        require_once ('MarketplaceWebService/Model/GetReportScheduleListResponse.php');
        return MarketplaceWebService_Model_GetReportScheduleListResponse::fromXML(
        $this->invoke($this->convertGetReportScheduleList($request)));
    }

    // Private API ------------------------------------------------------------//

    /**
     * Get the base64 encoded md5 value of $data. If $data is a memory or temp file stream, this
     * method dumps the contents into a string before calculating the md5. Hence, this method
     * shouldn't be used for large memory streams.
     *
     * @todo issue user notice if stream is temp or memory and data is large
     *
     * @param $data
     * @return string
     */
    public function getContentMd5($data) {
        $md5Hash = null;

        if (is_string($data)) {
            $md5Hash = md5($data, true);
        } else if (is_resource($data)) {
            // Assume $data is a stream.
            $streamMetadata = stream_get_meta_data($data);

            if ($streamMetadata['stream_type'] === 'MEMORY' || $streamMetadata['stream_type'] === 'TEMP') {
                $md5Hash = md5(stream_get_contents($data), true);
            } else {
                $md5Hash = md5_file($streamMetadata['uri'], true);
            }
        }

        return base64_encode($md5Hash);
    }

    /**
     * Invoke request and return response
     */
    private function invoke(array $converted, $dataHandle = null, $contentMd5 = null)
    {
        $parameters = $converted[CONVERTED_PARAMETERS_KEY];
        $actionName = $parameters["Action"];
        $response = array();
        $responseBody = null;
        $statusCode = 200;

        /* Submit the request and read response body */
        try {
            // Ensure the endpoint URL is set.
            if (empty($this->config['ServiceURL'])) {
                throw new MarketplaceWebService_Exception(
                array('ErrorCode' => 'InvalidServiceUrl',
                      'Message' => "Missing serviceUrl configuration value. You may obtain a list of valid MWS URLs by consulting the MWS Developer's Guide, or reviewing the sample code published along side this library."));
            }

            /* Add required request parameters */
            $parameters = $this->addRequiredParameters($parameters);
            $converted[CONVERTED_PARAMETERS_KEY] = $parameters;

            $shouldRetry = false;
            $retries = 0;
            do {
                try {
                    $response = $this->performRequest($actionName, $converted, $dataHandle, $contentMd5);

                    $httpStatus = $response['Status'];

                    switch ($httpStatus) {
                        case 200:
                            $shouldRetry = false;
                            break;

                        case 500:
                        case 503:
                            $errorResponse = MarketplaceWebService_Model_ErrorResponse::fromXML($response['ResponseBody']);

                            // We will not retry throttling errors since this would just add to the throttling problem.
                            $shouldRetry = ($errorResponse->getError()->getCode() === 'RequestThrottled')
                            ? false : true;

                            if ($shouldRetry && $retries <= $this->config['MaxErrorRetry']) {
                                $this->pauseOnRetry(++$retries);
                            } else {
                                throw $this->reportAnyErrors($response['ResponseBody'], $response['Status']);
                            }
                            break;

                        default:
                            $shouldRetry = false;
                            throw $this->reportAnyErrors($response['ResponseBody'], $response['Status']);
                            break;
                    }

                    /* Rethrow on deserializer error */
                } catch (Exception $e) {
                    throw new MarketplaceWebService_Exception(array('Exception' => $e, 'Message' => $e->getMessage()));
                }

            } while ($shouldRetry);

        } catch (MarketplaceWebService_Exception $se) {
            throw $se;
        } catch (Exception $t) {
            throw new MarketplaceWebService_Exception(array('Exception' => $t, 'Message' => $t->getMessage()));
        }
        return $response['ResponseBody'];
    }

    /**
     * Look for additional error strings in the response and return formatted exception
     */
    private function reportAnyErrors($responseBody, $status, Exception $e =  null)
    {
        $ex = null;
        if (null !== $responseBody && strpos($responseBody, '<') === 0) {
            if (preg_match('@<RequestId>(.*)</RequestId>.*<Error><Code>(.*)</Code><Message>(.*)</Message></Error>.*(<Error>)?@mi',
            $responseBody, $errorMatcherOne)) {

                $requestId = $errorMatcherOne[1];
                $code = $errorMatcherOne[2];
                $message = $errorMatcherOne[3];

                $ex = new MarketplaceWebService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                           'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody));

            } elseif (preg_match('@<Error><Code>(.*)</Code><Message>(.*)</Message></Error>.*(<Error>)?.*<RequestID>(.*)</RequestID>@mi',
            $responseBody, $errorMatcherTwo)) {

                $code = $errorMatcherTwo[1];
                $message = $errorMatcherTwo[2];
                $requestId = $errorMatcherTwo[4];
                $ex = new MarketplaceWebService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                              'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody));
            } elseif (preg_match('@<Error><Type>(.*)</Type><Code>(.*)</Code><Message>(.*)</Message>.*</Error>.*(<Error>)?.*<RequestId>(.*)</RequestId>@mi',
            $responseBody, $errorMatcherThree)) {

                $type = $errorMatcherThree[1];
                $code = $errorMatcherThree[2];
                $message = $errorMatcherThree[3];
                $requestId = $errorMatcherThree[5];
                $ex = new MarketplaceWebService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                              'ErrorType' => $type, 'RequestId' => $requestId, 'XML' => $responseBody));

            } else {
                $ex = new MarketplaceWebService_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status));
            }
        } else {
            $ex = new MarketplaceWebService_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status));
        }
        return $ex;
    }

    /**
     * Setup and execute the request via cURL and return the server response.
     *
     * @param $action - the MWS action to perform.
     * @param $parameters - the MWS parameters for the Action.
     * @param $dataHandle - A stream handle to either a feed to upload, or a report/feed submission result to download.
     * @param $contentMd5 - The Content-MD5 HTTP header value used for feed submissions.
     * @return array
     */
    protected function performRequest($action, array $converted, $dataHandle = null, $contentMd5 = null) {

        $curlOptions = $this->configureCurlOptions($action, $converted, $dataHandle, $contentMd5);

        if (!$curlOptions[CURLOPT_RETURNTRANSFER]) {
            $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        }

        $this->curlClient = curl_init();
        curl_setopt_array($this->curlClient, $curlOptions);

        // TODO is @ useful to access memory stream?
        $this->headerContents = @fopen('php://memory', 'rw+');
        $this->errorResponseBody = @fopen('php://memory', 'rw+');

        $httpResponse = curl_exec($this->curlClient);

        rewind($this->headerContents);
        $header = stream_get_contents($this->headerContents);

        $parsedHeader = $this->parseHttpHeader($header);

        $code = (int) curl_getinfo($this->curlClient, CURLINFO_HTTP_CODE);

        // Only attempt to verify the Content-MD5 value if the request was successful.
        if (RequestType::getRequestType($action) === RequestType::POST_DOWNLOAD) {
            if ($code != 200) {
                rewind($this->errorResponseBody);
                $httpResponse =  stream_get_contents($this->errorResponseBody);
            } else {
                $this->verifyContentMd5($parsedHeader['Content-MD5'], $dataHandle);
                $httpResponse = $this->getDownloadResponseDocument($action, $parsedHeader);
            }
        }

        // Cleanup open streams and cURL instance.
        @fclose($this->headerContents);
        @fclose($this->errorResponseBody);
        curl_close($this->curlClient);


        return array (
        'Status' => $code, 
        'ResponseBody' => $httpResponse);
    }

    /**
     * Compares the received Content-MD5 Hash value from the response with a locally calculated
     * value based on the contents of the response body. If the received hash value doesn't match
     * the locally calculated hash value, an exception is raised.
     *
     * @param $receivedMd5Hash
     * @param $streamHandle
     * @return unknown_type
     */
    private function verifyContentMd5($receivedMd5Hash, $streamHandle) {
        rewind($streamHandle);
        $expectedMd5Hash = $this->getContentMd5($streamHandle);
        rewind($streamHandle);

        if (!($receivedMd5Hash === $expectedMd5Hash)) {
            require_once ('MarketplaceWebService/Exception.php');
            throw new MarketplaceWebService_Exception(
            array(
            'Message' => 'Received Content-MD5 = ' . $receivedMd5Hash . ' but expected ' . $expectedMd5Hash, 
            'ErrorCode' => 'ContentMD5DoesNotMatch'));
        }
    }

    /**
     * Build an associative array of an HTTP Header lines. For requests, the HTTP request line
     * is not contained in the array, nor is the HTTP status line for response headers.
     *
     * @param $header
     * @return array
     */
    private function parseHttpHeader($header) {
        $parsedHeader = array ();
        foreach (explode("\n", $header) as $line) {
            $splitLine = preg_split('/:\s/', $line, 2, PREG_SPLIT_NO_EMPTY);

            if (sizeof($splitLine) == 2) {
                $parsedHeader[trim($splitLine[0])] = trim($splitLine[1]);
            }
        }

        return $parsedHeader;
    }

    /**
     * cURL callback to write the response HTTP body into a stream. This is only intended to be used
     * with RequestType::POST_DOWNLOAD request types, since the responses can potentially become
     * large.
     *
     * @param $ch - The curl handle.
     * @param $string - body portion to write.
     * @return int - number of byes written.
     */
    private function responseCallback($ch, $string) {
        $httpStatusCode = (int) curl_getinfo($this->curlClient, CURLINFO_HTTP_CODE);
         
        // For unsuccessful responses, i.e. non-200 HTTP responses, we write the response body
        // into a separate stream.
        $responseHandle;
        if ($httpStatusCode == 200) {
            $responseHandle = $this->responseBodyContents;
        } else {
            $responseHandle = $this->errorResponseBody;
        }
         
        return fwrite($responseHandle, $string);
    }

    /**
     * cURL callback to write the response HTTP header into a stream.
     *
     * @param $ch - The curl handle.
     * @param $string - header portion to write.
     * @return int - number of bytes written to stream.
     */
    private function headerCallback($ch, $string) {
        $bytesWritten = fwrite($this->headerContents, $string);
        return $bytesWritten;
    }

    /**
     * Gets cURL options common to all MWS requests.
     * @todo make curl options editable
     * @return unknown_type
     */
    private function getDefaultCurlOptions() {
        return array (
        CURLOPT_POST => true,
        CURLOPT_USERAGENT => $this->config['UserAgent'],
        CURLOPT_VERBOSE => true,
        CURLOPT_HEADERFUNCTION => array ($this, 'headerCallback'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        );
    }

    /**
     * Configures specific curl options based on the request type.
     * @todo check if this can be simplified
     *
     * @param $action
     * @param $parameters
     * @param $streamHandle
     * @return array
     */
    private function configureCurlOptions($action, array $converted, $streamHandle = null, $contentMd5 = null) {
        $curlOptions = $this->getDefaultCurlOptions();

        if (null !== $this->config['ProxyHost']) {
            $proxy = $this->config['ProxyHost'];
            $proxy .= ':' . ($this->config['ProxyPort'] == -1 ? '80' : $this->config['ProxyPort']);

            $curlOptions[CURLOPT_PROXY] = $proxy;
        }

        $serviceUrl = $this->config['ServiceURL'];

        // append the '/' character to the end of the service URL if it doesn't exist.
        if (!(substr($serviceUrl, strlen($serviceUrl) - 1) === '/')) {
            $serviceUrl .= '/';
        }

        $requestType = RequestType::getRequestType($action);

        if ($requestType == RequestType::POST_UPLOAD) {

            if (null === $streamHandle || !is_resource($streamHandle)) {
                require_once ('MarketplaceWebService/Exception.php');
                throw new MarketplaceWebService_Exception(
                array ('Message' => 'Missing stream resource.'));
            }

            $serviceUrl .= '?' . $this->getParametersAsString($converted[CONVERTED_PARAMETERS_KEY]);

            $curlOptions[CURLOPT_URL] = $serviceUrl;

            $header[] = 'Expect: ';
            $header[] = 'Accept: ';
            $header[] = 'Transfer-Encoding: chunked';
            $header[] = 'Content-MD5: ' . $contentMd5;

            $curlOptions[CURLOPT_HTTPHEADER] = array_merge($header, $converted[CONVERTED_HEADERS_KEY]);

            rewind($streamHandle);
            $curlOptions[CURLOPT_INFILE] = $streamHandle;

            $curlOptions[CURLOPT_UPLOAD] = true;

            $curlOptions[CURLOPT_CUSTOMREQUEST] = self::REQUEST_TYPE;

        } else if (!($requestType === RequestType::UNKNOWN)) {
            $curlOptions[CURLOPT_URL] = $this->config['ServiceURL'];
            $curlOptions[CURLOPT_POSTFIELDS] = $this->getParametersAsString($converted[CONVERTED_PARAMETERS_KEY]);

            if ($requestType == RequestType::POST_DOWNLOAD) {
                $this->responseBodyContents = $streamHandle;
                $curlOptions[CURLOPT_WRITEFUNCTION] = array ($this, 'responseCallback');
            }
        } else {
            throw new InvalidArgumentException("$action is not a valid request type.");
        }

        return $curlOptions;
    }

    /**
     * For RequestType::POST_DOWNLOAD actions, construct a response containing the Amazon Request ID
     * and Content MD5 header value.
     *
     * @param $responseType
     * @param $header
     * @return unknown_type
     */
    private function getDownloadResponseDocument($responseType, $header) {
        $md5 = $header['Content-MD5'];
        $requestId = $header['x-amz-request-id'];

        $response = '<' . $responseType . 'Response xmlns="http://mws.amazonaws.com/doc/2009-01-01/">';

        $response .= '<' . $responseType . 'Result>';
        $response .= '<ContentMd5>';
        $response .= $md5;
        $response .= '</ContentMd5>';
        $response .= '</' . $responseType . 'Result>';
        $response .= '<ResponseMetadata>';
        $response .= '<RequestId>';
        $response .= $requestId;
        $response .= '</RequestId>';
        $response .= '</ResponseMetadata>';
        $response .= '</' . $responseType . 'Response>';

        return $response;
    }

    /**
     * Exponential sleep on failed request
     * @todo make editable
     * @param retries current retry
     */
    private function pauseOnRetry($retries)
    {
        $delay = (int) (pow(4, $retries) * 100000) ;
        usleep($delay);
    }

    /**
     * Add authentication related and version parameters
     *
     * @param array $parameters
     * @return array
     */
    public function addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId'] = $this->awsAccessKeyId;
        $parameters['Timestamp'] = $this->getFormattedTimestamp(new DateTime('now', new DateTimeZone('UTC')));
        $parameters['Version'] = self::SERVICE_VERSION;
        $parameters['SignatureVersion'] = $this->config['SignatureVersion'];
        // TODO signature version must be > 1
        if ($parameters['SignatureVersion'] > 1) {
            $parameters['SignatureMethod'] = $this->config['SignatureMethod'];
        }
        $parameters['Signature'] = $this->signParameters($parameters, $this->awsSecretAccessKey);

        return $parameters;
    }

    /**
     * Convert paremeters to Url encoded query string
     *
     * @param array $parameters
     * @return string
     */
    public function getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->urlencode($value);
        }
        return implode('&', $queryParameters);
    }


    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * Signature Version 0: This is not supported in the Marketplace Web Service.
     *
     * Signature Version 1: This is not supported in the Marketplace Web Service.
     *
     * Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    public function signParameters(array $parameters, $key) {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm = "HmacSHA1";
        $stringToSign = null;
        // TODO use switch statement
        switch ($signatureVersion) {
            case 0:
            case 1:
                throw new InvalidArguementException(
                "Signature Version $signatureVersion is no longer supported. Only Signature Version 2 is supported.");
            case 2:
                $algorithm = $this->config['SignatureMethod'];
                $parameters['SignatureMethod'] = $algorithm;
                $stringToSign = $this->calculateStringToSignV2($parameters);
                break;

            default:
                throw new Exception("Invalid Signature Version specified");
        }

        return $this->sign($stringToSign, $key, $algorithm);
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    public function calculateStringToSignV2(array $parameters, $queuepath = null) {

        $parsedUrl = parse_url($this->config['ServiceURL']);
        $endpoint = $parsedUrl['host'];
        // isset of null value is false
        if ( isset($parsedUrl['port']) ) {
            $endpoint .= ':' . $parsedUrl['port'];
        }

        $data = 'POST';
        $data .= "\n";
        $data .= $endpoint;
        $data .= "\n";
        $uri  = $queuepath?$queuepath:'/';
        $uriencoded = implode("/", array_map(array($this, "urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->getParametersAsString($parameters);

        return $data;
    }

    public function urlencode($value) {
        return str_replace('%7E', '~', rawurlencode($value));
    }


    /**
     * Computes RFC 2104-compliant HMAC signature
     */
    public function sign($data, $key, $algorithm)
    {
        switch ($algorithm) {
            case 'HmacSHA1':
                $hash = 'sha1';
                break;

            case 'HmacSHA256':
                $hash = 'sha256';
                break;

            default:
                throw new Exception ("Non-supported signing method specified");
        }

        return base64_encode(
        hash_hmac($hash, $data, $key, true)
        );
    }

    /**
     * Returns a ISO 8601 formatted string from a DateTime instance.
     * 
     * @param DateTime $dateTime
     * @return unknown
     */
    public function getFormattedTimestamp(DateTime $dateTime) {
        // TODO use config to determine date format
        return $dateTime->format(DateTime::ISO8601);
    }

    /**
     * Convert request to array.
     * Array is extended and formated for use with invoke().
     * 
     * @param string  $action
     * @param MarketplaceWebService_ModelRequest $request
     *
     * @return array
     */
    public function convert($action, MarketplaceWebService_ModelRequest $request)
    {
        if ($action == '') {
            throw new InvalidArgumentException("$action can not be empty.");
        }
        $parameters = array();
        $parameters['Action'] = ucfirst($action);
        $parameters = $request->convert($parameters);

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }

    /**
     * Convert GetReportRequest to name value pairs
     */
    private function convertGetReport($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReport';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportId()) {
            $parameters['ReportId'] =  $request->getReportId();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportScheduleCountRequest to name value pairs
     */
    private function convertGetReportScheduleCount($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportScheduleCount';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportRequestListByNextTokenRequest to name value pairs
     */
    private function convertGetReportRequestListByNextToken($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportRequestListByNextToken';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetNextToken()) {
            $parameters['NextToken'] =  $request->getNextToken();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }

    /**
     * Convert UpdateReportAcknowledgementsRequest to name value pairs
     */
    private function convertUpdateReportAcknowledgements($request) {

        $parameters = array();
        $parameters['Action'] = 'UpdateReportAcknowledgements';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportIdList()) {
            $reportIdList = $request->getReportIdList();
            foreach  ($reportIdList->getId() as $idIndex => $id) {
                $parameters['ReportIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetAcknowledged()) {
            $parameters['Acknowledged'] =  $request->getAcknowledged() ? "true" : "false";
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert SubmitFeedRequest to name value pairs
     */
    private function convertSubmitFeed($request) {

        $parameters = array();
        $parameters['Action'] = 'SubmitFeed';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetMarketplaceIdList()) {
            $marketplaceIdList = $request->getMarketplaceIdList();
            foreach  ($marketplaceIdList->getId() as $idIndex => $id) {
                $parameters['MarketplaceIdList.Id.'.($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetFeedType()) {
            $parameters['FeedType'] =  $request->getFeedType();
        }
        if ($request->isSetPurgeAndReplace()) {
            $parameters['PurgeAndReplace'] =  $request->getPurgeAndReplace() ? "true" : "false";
        }

        $headers = array();
        array_push($headers, "Content-Type: " . $request->getContentType()->toString());

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $headers);
    }


    /**
     * Convert GetReportCountRequest to name value pairs
     */
    private function convertGetReportCount($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportCount';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetAcknowledged()) {
            $parameters['Acknowledged'] =  $request->getAcknowledged() ? "true" : "false";
        }
        if ($request->isSetAvailableFromDate()) {
            $parameters['AvailableFromDate'] =
            $this->getFormattedTimestamp($request->getAvailableFromDate());
        }
        if ($request->isSetAvailableToDate()) {
            $parameters['AvailableToDate'] =
            $this->getFormattedTimestamp($request->getAvailableToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetFeedSubmissionListByNextTokenRequest to name value pairs
     */
    private function convertGetFeedSubmissionListByNextToken($request) {

        $parameters = array();
        $parameters['Action'] = 'GetFeedSubmissionListByNextToken';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetNextToken()) {
            $parameters['NextToken'] =  $request->getNextToken();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert CancelFeedSubmissionsRequest to name value pairs
     */
    private function convertCancelFeedSubmissions($request) {

        $parameters = array();
        $parameters['Action'] = 'CancelFeedSubmissions';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetFeedSubmissionIdList()) {
            $feedSubmissionIdList = $request->getFeedSubmissionIdList();
            foreach  ($feedSubmissionIdList->getId() as $idIndex => $id) {
                $parameters['FeedSubmissionIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetFeedTypeList()) {
            $feedTypeList = $request->getFeedTypeList();
            foreach  ($feedTypeList->getType() as $typeIndex => $type) {
                $parameters['FeedTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetSubmittedFromDate()) {
            $parameters['SubmittedFromDate'] =
            $this->getFormattedTimestamp($request->getSubmittedFromDate());
        }
        if ($request->isSetSubmittedToDate()) {
            $parameters['SubmittedToDate'] =
            $this->getFormattedTimestamp($request->getSubmittedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert RequestReportRequest to name value pairs
     */
    private function convertRequestReport($request) {

        $parameters = array();
        $parameters['Action'] = 'RequestReport';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetMarketplaceIdList()) {
            $marketplaceIdList = $request->getMarketplaceIdList();
            foreach  ($marketplaceIdList->getId() as $idIndex => $id) {
                $parameters['MarketplaceIdList.Id.'.($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetReportType()) {
            $parameters['ReportType'] =  $request->getReportType();
        }
        if ($request->isSetStartDate()) {
            $parameters['StartDate'] =
            $this->getFormattedTimestamp($request->getStartDate());
        }
        if ($request->isSetEndDate()) {
            $parameters['EndDate'] =
            $this->getFormattedTimestamp($request->getEndDate());
        }
        if ($request->isSetReportOptions()) {
            $parameters['ReportOptions'] =  $request->getReportOptions();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetFeedSubmissionCountRequest to name value pairs
     */
    private function convertGetFeedSubmissionCount($request) {

        $parameters = array();
        $parameters['Action'] = 'GetFeedSubmissionCount';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetFeedTypeList()) {
            $feedTypeList = $request->getFeedTypeList();
            foreach  ($feedTypeList->getType() as $typeIndex => $type) {
                $parameters['FeedTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetFeedProcessingStatusList()) {
            $feedProcessingStatusList = $request->getFeedProcessingStatusList();
            foreach  ($feedProcessingStatusList->getStatus() as $statusIndex => $status) {
                $parameters['FeedProcessingStatusList' . '.' . 'Status' . '.'  . ($statusIndex + 1)] =  $status;
            }
        }
        if ($request->isSetSubmittedFromDate()) {
            $parameters['SubmittedFromDate'] =
            $this->getFormattedTimestamp($request->getSubmittedFromDate());
        }
        if ($request->isSetSubmittedToDate()) {
            $parameters['SubmittedToDate'] =
            $this->getFormattedTimestamp($request->getSubmittedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert CancelReportRequestsRequest to name value pairs
     */
    private function convertCancelReportRequests($request) {

        $parameters = array();
        $parameters['Action'] = 'CancelReportRequests';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportRequestIdList()) {
            $reportRequestIdList = $request->getReportRequestIdList();
            foreach  ($reportRequestIdList->getId() as $idIndex => $id) {
                $parameters['ReportRequestIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetReportProcessingStatusList()) {
            $reportProcessingStatusList = $request->getReportProcessingStatusList();
            foreach  ($reportProcessingStatusList->getStatus() as $statusIndex => $status) {
                $parameters['ReportProcessingStatusList' . '.' . 'Status' . '.'  . ($statusIndex + 1)] =  $status;
            }
        }
        if ($request->isSetRequestedFromDate()) {
            $parameters['RequestedFromDate'] =
            $this->getFormattedTimestamp($request->getRequestedFromDate());
        }
        if ($request->isSetRequestedToDate()) {
            $parameters['RequestedToDate'] =
            $this->getFormattedTimestamp($request->getRequestedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportListRequest to name value pairs
     */
    private function convertGetReportList($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportList';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetMaxCount()) {
            $parameters['MaxCount'] =  $request->getMaxCount();
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetAcknowledged()) {
            $parameters['Acknowledged'] =  $request->getAcknowledged() ? "true" : "false";
        }
        if ($request->isSetAvailableFromDate()) {
            $parameters['AvailableFromDate'] =
            $this->getFormattedTimestamp($request->getAvailableFromDate());
        }
        if ($request->isSetAvailableToDate()) {
            $parameters['AvailableToDate'] =
            $this->getFormattedTimestamp($request->getAvailableToDate());
        }
        if ($request->isSetReportRequestIdList()) {
            $reportRequestIdList = $request->getReportRequestIdList();
            foreach  ($reportRequestIdList->getId() as $idIndex => $id) {
                $parameters['ReportRequestIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetFeedSubmissionResultRequest to name value pairs
     */
    private function convertGetFeedSubmissionResult($request) {

        $parameters = array();
        $parameters['Action'] = 'GetFeedSubmissionResult';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetFeedSubmissionId()) {
            $parameters['FeedSubmissionId'] =  $request->getFeedSubmissionId();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetFeedSubmissionListRequest to name value pairs
     */
    private function convertGetFeedSubmissionList($request) {

        $parameters = array();
        $parameters['Action'] = 'GetFeedSubmissionList';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetFeedSubmissionIdList()) {
            $feedSubmissionIdList = $request->getFeedSubmissionIdList();
            foreach  ($feedSubmissionIdList->getId() as $idIndex => $id) {
                $parameters['FeedSubmissionIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetMaxCount()) {
            $parameters['MaxCount'] =  $request->getMaxCount();
        }
        if ($request->isSetFeedTypeList()) {
            $feedTypeList = $request->getFeedTypeList();
            foreach  ($feedTypeList->getType() as $typeIndex => $type) {
                $parameters['FeedTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetFeedProcessingStatusList()) {
            $feedProcessingStatusList = $request->getFeedProcessingStatusList();
            foreach  ($feedProcessingStatusList->getStatus() as $statusIndex => $status) {
                $parameters['FeedProcessingStatusList' . '.' . 'Status' . '.'  . ($statusIndex + 1)] =  $status;
            }
        }
        if ($request->isSetSubmittedFromDate()) {
            $parameters['SubmittedFromDate'] =
            $this->getFormattedTimestamp($request->getSubmittedFromDate());
        }
        if ($request->isSetSubmittedToDate()) {
            $parameters['SubmittedToDate'] =
            $this->getFormattedTimestamp($request->getSubmittedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportRequestListRequest to name value pairs
     */
    private function convertGetReportRequestList($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportRequestList';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportRequestIdList()) {
            $reportRequestIdList = $request->getReportRequestIdList();
            foreach  ($reportRequestIdList->getId() as $idIndex => $id) {
                $parameters['ReportRequestIdList' . '.' . 'Id' . '.'  . ($idIndex + 1)] =  $id;
            }
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetReportProcessingStatusList()) {
            $reportProcessingStatusList = $request->getReportProcessingStatusList();
            foreach  ($reportProcessingStatusList->getStatus() as $statusIndex => $status) {
                $parameters['ReportProcessingStatusList' . '.' . 'Status' . '.'  . ($statusIndex + 1)] =  $status;
            }
        }
        if ($request->isSetMaxCount()) {
            $parameters['MaxCount'] =  $request->getMaxCount();
        }
        if ($request->isSetRequestedFromDate()) {
            $parameters['RequestedFromDate'] =
            $this->getFormattedTimestamp($request->getRequestedFromDate());
        }
        if ($request->isSetRequestedToDate()) {
            $parameters['RequestedToDate'] =
            $this->getFormattedTimestamp($request->getRequestedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportScheduleListByNextTokenRequest to name value pairs
     */
    private function convertGetReportScheduleListByNextToken($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportScheduleListByNextToken';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetNextToken()) {
            $parameters['NextToken'] =  $request->getNextToken();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportListByNextTokenRequest to name value pairs
     */
    private function convertGetReportListByNextToken($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportListByNextToken';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetNextToken()) {
            $parameters['NextToken'] =  $request->getNextToken();
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert ManageReportScheduleRequest to name value pairs
     */
    private function convertManageReportSchedule($request) {

        $parameters = array();
        $parameters['Action'] = 'ManageReportSchedule';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportType()) {
            $parameters['ReportType'] =  $request->getReportType();
        }
        if ($request->isSetSchedule()) {
            $parameters['Schedule'] =  $request->getSchedule();
        }
        if ($request->isSetScheduledDate()) {
            $parameters['ScheduledDate'] =
            $this->getFormattedTimestamp($request->getScheduledDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportRequestCountRequest to name value pairs
     */
    private function convertGetReportRequestCount($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportRequestCount';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }
        if ($request->isSetReportProcessingStatusList()) {
            $reportProcessingStatusList = $request->getReportProcessingStatusList();
            foreach  ($reportProcessingStatusList->getStatus() as $statusIndex => $status) {
                $parameters['ReportProcessingStatusList' . '.' . 'Status' . '.'  . ($statusIndex + 1)] =  $status;
            }
        }
        if ($request->isSetRequestedFromDate()) {
            $parameters['RequestedFromDate'] =
            $this->getFormattedTimestamp($request->getRequestedFromDate());
        }
        if ($request->isSetRequestedToDate()) {
            $parameters['RequestedToDate'] =
            $this->getFormattedTimestamp($request->getRequestedToDate());
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }


    /**
     * Convert GetReportScheduleListRequest to name value pairs
     */
    private function convertGetReportScheduleList($request) {

        $parameters = array();
        $parameters['Action'] = 'GetReportScheduleList';
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetMerchant()) {
            $parameters['Merchant'] =  $request->getMerchant();
        }
        if ($request->isSetReportTypeList()) {
            $reportTypeList = $request->getReportTypeList();
            foreach  ($reportTypeList->getType() as $typeIndex => $type) {
                $parameters['ReportTypeList' . '.' . 'Type' . '.'  . ($typeIndex + 1)] =  $type;
            }
        }

        return array(CONVERTED_PARAMETERS_KEY => $parameters, CONVERTED_HEADERS_KEY => $this->defaultHeaders);
    }
}
