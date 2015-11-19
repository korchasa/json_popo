<?php namespace korchasa\jsonToPopo;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class Json
 *
 * @package gossi\codegen\source
 */
class Json
{
    /**
     * @param string $json
     * @param string $full_class_name
     *
     * @return PhpFile
     */
    public function process($json, $full_class_name = 'stdClass')
    {
        $object = json_decode($json);

        list($class, $namespace) = $this->determineNames($full_class_name);

        $file = new PhpFile();
        $namespace = $file->addNamespace($namespace);
        $this->object($namespace, $class, $object);

        return $file;
    }

    /**
     * @param PhpNamespace $namespace
     * @param string       $class_name
     * @param object       $object
     */
    public function object($namespace, $class_name, $object)
    {
        $class_name = ucfirst($class_name);
        $class = $namespace->addClass($class_name);
        $class->addDocument('Class '.$class_name);

        foreach (get_object_vars($object) as $name => $value) {
            $property = $class->addProperty($name);
            $property->setVisibility('public');
            switch (gettype($value)) {
                case 'object':
                    $class_name = ucfirst($name);
                    $property->addDocument('@var '.$class_name.PHP_EOL);
                    $this->object($namespace, $class_name, $value);
                    break;
                case 'array':
                    $type_doc = isset($value[0])
                        ? gettype($value[0]).'[]'
                        : 'array';
                    $property->addDocument('@var '.$type_doc.PHP_EOL);
                    break;
                default:
                    $property->addDocument('@var '.gettype($value).PHP_EOL);
            }
        }
    }

    protected function determineNames($full_class_name)
    {
        $last_delimiter_pos = strrpos($full_class_name, '\\');
        if (false === $last_delimiter_pos) {
            return [$full_class_name, null];
        } else {
            return [
                substr($full_class_name, $last_delimiter_pos + 1),
                substr($full_class_name, 1, $last_delimiter_pos - 1),
            ];
        }
    }
}
