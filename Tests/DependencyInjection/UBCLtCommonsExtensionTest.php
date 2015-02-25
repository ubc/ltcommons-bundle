<?php
namespace UBC\LtCommonsBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use UBC\LtCommonsBundle\DependencyInjection\UBCLtCommonsExtension;
use UBC\LtCommonsBundle\UBCLtCommonsBundle;

class UBCLtCommonsExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testValidConfigHttpBasic()
    {
        $container = $this->getRawContainer();

        $container->loadFromExtension('ubc_lt_commons', array(
            'providers' => array(
                'sis' => array(
                    'base_url' => 'http://sisapi.example.com',
                    'auth' => array(
                        'module' => 'HttpBasic',
                        'username' => 'username',
                        'password' => 'password',
                    )
                )
            )

        ));

        // compile the service definitions
        $container->compile();

        $this->assertInstanceOf('UBC\LtCommons\Service\DepartmentCodeService', $container->get('ubc_lt_commons.service.department_code'));
        $this->assertInstanceOf('UBC\LtCommons\Service\SubjectCodeService', $container->get('ubc_lt_commons.service.subject_code'));
    }

    public function testValidConfigAuth2()
    {
        $container = $this->getRawContainer();

        $container->loadFromExtension('ubc_lt_commons', array(
            'providers' => array(
                'sis' => array(
                    'base_url' => 'http://sisapi.example.com',
                    'auth' => array(
                        'module' => 'Auth2',
                        'username' => 'username',
                        'password' => 'password',
                        'service_application' => 'srv_app'
                    )
                )
            )

        ));

        // compile the service definitions
        $container->compile();

        $this->assertInstanceOf('UBC\LtCommons\Service\DepartmentCodeService', $container->get('ubc_lt_commons.service.department_code'));
        $this->assertInstanceOf('UBC\LtCommons\Service\SubjectCodeService', $container->get('ubc_lt_commons.service.subject_code'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Unrecognized option "invalid" under "ubc_lt_commons.providers.sis"
     */
    public function testInValidConfig()
    {
        $container = $this->getRawContainer();

        $container->loadFromExtension('ubc_lt_commons', array(
            'providers' => array(
                'sis' => array(
                    'invalid' => 'parameter'
                )
            )

        ));

        // compile the service definitions
        $container->compile();
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "ubc_lt_commons.providers": The base_url has to specified to use xml data provider.
     */
    public function testInValidConfigMissingPath()
    {
        $container = $this->getRawContainer();

        $container->loadFromExtension('ubc_lt_commons', array(
            'providers' => array(
                'xml' => array()
            )

        ));

        // compile the service definitions
        $container->compile();
    }

    protected function getRawContainer()
    {
        $container = new ContainerBuilder();
        $ltcommons = new UBCLtCommonsExtension();
        $container->registerExtension($ltcommons);
        $bundle = new UBCLtCommonsBundle();
        $bundle->build($container);
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());

        return $container;
    }

    protected function getContainer()
    {
        $container = $this->getRawContainer();
        $container->compile();

        return $container;
    }
}
