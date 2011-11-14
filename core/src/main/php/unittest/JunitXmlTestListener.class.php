<?php
/* This class is part of the XP framework
 *
 * $Id: XmlTestListener.class.php 13822 2009-11-12 14:55:15Z friebe $
 */

  uses(
    'unittest.TestListener', 
    'io.streams.OutputStreamWriter', 
    'xml.Tree',
    'util.collections.HashTable'
  );

  /**
   * Creates an XML file suitable for importing into Netbeans
   *
   * @test     None yet
   * @purpose  TestListener
   */
  class JunitXmlTestListener extends Object implements TestListener {
    public $out= NULL;
    protected $tree= NULL;
    protected $root= NULL;
    protected $classes= NULL;


    /**
     * Constructor
     *
     * @param   io.streams.OutputStreamWriter out
     */
    public function __construct(OutputStreamWriter $out) {
      $this->out= $out;
      $this->tree= create(new Tree('testsuites'))->withRoot($this->root= new Node('testsuites'));
      
      $this->classes= create('new util.collections.HashTable<lang.XPClass, xml.Node>()');
    }

    /**
     * Tries to get class uri via reflection
     *
     * @param lang.XPClass class The class to return the URI for
     * @return string
     */
    private function getFileUri(XPClass $class) {
      try {
        $Urimethod= $class->getClassLoader()->getClass()->getMethod('classURI');
        $Urimethod->setAccessible(TRUE);
        return $Urimethod->invoke($class->getClassLoader(), $class->getName());
      } catch (Exception $ignored) {
        return $class->getClassName();
      }
    }

    /**
     * Tries to get method start line
     *
     * @param lang.XPClass $class
     * @param string $methodname
     * @return int
     */
    private function getStartLine(XPClass $class, $methodname) {
      try {
        return $class->_reflect->getMethod($methodname)->getStartLine();
      } catch (Exception $ignored) {
        return 0;
      }
    }
    
    /**
     * Return message error string for given error
     * 
     * @param unittest.TestOutcome error The error
     * @return string
     */
    protected function messageFor(TestOutcome $error) {
      $testClass= $error->test->getClass();
      
      return sprintf(
        "%s(%s)\n%s \n\n%s:%d\n\n",
        $testClass->getName(),
        $error->test->getName(),
        xp::stringOf($error->reason),
        $this->getFileUri($testClass),
        $this->getStartLine($testClass, $error->test->getName())
      );
    }
    
    /**
     * Called when a test case starts.
     *
     * @param   unittest.TestCase failure
     */
    public function testStarted(TestCase $case) {
      // NOOP
    }

    /**
     * Add test result node, if it does not yet exist.
     *
     * @param   unittest.TestCase case
     * @return  xml.Node
     */
    protected function testNode(TestCase $case) {
      $class= $case->getClass();
      if (!$this->classes->containsKey($class)) {
        $this->classes[$class]= $this->root->addChild(new Node('testsuite', NULL, array(
          'name'       => $class->getName(),
          'file'       => $this->getFileUri($class),
          'fullPackage'=> $class->getPackage()->getName(),
          'package'    => $class->getPackage()->getSimpleName(),
          'tests'      => 0,
          'failures'   => 0,
          'errors'     => 0,
          'skipped'    => 0,
          'time'       => 0.0
        )));
      }

      return $this->classes[$class];
    }
    
    /**
     * Add test case information node and update suite information.
     *
     * @param   unittest.TestOutcome outcome
     * @param   string inc
     * @return  xml.Node
     */
    protected function addTestCase(TestOutcome $outcome, $inc= NULL) {
      $testClass= $outcome->test->getClass();

      // Update test count
      $n= $this->testNode($outcome->test);
      $n->setAttribute('tests', $n->getAttribute('tests')+ 1);
      $n->setAttribute('time', $n->getAttribute('time')+ $outcome->elapsed());
      $inc && $n->setAttribute($inc, $n->getAttribute($inc)+ 1);

      //Update test counter
      $this->root->setAttribute('tests', $this->root->getAttribute('tests')+ 1);
      $inc && $this->root->setAttribute($inc, $this->root->getAttribute($inc)+ 1);
      
      // Add testcase information
      return $n->addChild(new Node('testcase', NULL, array(
        'name'       => $outcome->test->getName(),
        'class'      => $testClass->getName(),
        'file'       => $this->getFileUri($testClass),
        'line'       => $this->getStartLine($testClass, $outcome->test->getName()),
        'time'       => sprintf('%.6f', $outcome->elapsed())
      )));
    }

    /**
     * Called when a test fails.
     *
     * @param   unittest.TestFailure failure
     */
    public function testFailed(TestFailure $failure) {
      $t= $this->addTestCase($failure, 'failures');
      $t->addChild(new Node('failure', $this->messageFor($failure), array(
        'message' => trim($failure->reason->compoundMessage()),
        'type'    => $failure->reason->getClassName()
      )));
    }

    /**
     * Called when a test errors.
     *
     * @param   unittest.TestError error
     */
    public function testError(TestError $error) {
      $t= $this->addTestCase($error, 'failures');
      $t->addChild(new Node('error', $this->messageFor($error), array(
        'message' => trim($error->reason->compoundMessage()),
        'type'    => $error->reason->getClassName()
      )));
    }

    /**
     * Called when a test raises warnings.
     *
     * @param   unittest.TestWarning warning
     */
    public function testWarning(TestWarning $warning) {
      $t= $this->addTestCase($warning, 'errors');
      $t->addChild(new Node('error', $this->messageFor($warning), array(
        'message' => 'Non-clear error stack',
        'type'    => $warning->getClassName()
      )));
    }
    
    /**
     * Called when a test finished successfully.
     *
     * @param   unittest.TestSuccess success
     */
    public function testSucceeded(TestSuccess $success) {
      $this->addTestCase($success);
    }
    
    /**
     * Called when a test is not run because it is skipped due to a 
     * failed prerequisite.
     *
     * @param   unittest.TestSkipped skipped
     */
    public function testSkipped(TestSkipped $skipped) {
      if ($skipped->reason instanceof Throwable) {
        $reason= trim($skipped->reason->compoundMessage());
      } else {
        $reason= $skipped->reason;
      }
      $this->addTestCase($skipped, 'skipped')->setAttribute('skipped', $reason);
    }

    /**
     * Called when a test is not run because it has been ignored by using
     * the @ignore annotation.
     *
     * @param   unittest.TestSkipped ignore
     */
    public function testNotRun(TestSkipped $ignore) {
      $this->addTestCase($ignore, 'skipped')->setAttribute('skipped', $ignore->reason);
    }

    /**
     * Called when a test run starts.
     *
     * @param   unittest.TestSuite suite
     */
    public function testRunStarted(TestSuite $suite) {
      // NOOP
    }
    
    /**
     * Called when a test run finishes.
     *
     * @param   unittest.TestSuite suite
     * @param   unittest.TestResult result
     */
    public function testRunFinished(TestSuite $suite, TestResult $result) {
      $this->out->write($this->tree->getDeclaration()."\n");
      $this->out->write($this->tree->getSource(INDENT_DEFAULT));
    }
  }
?>
