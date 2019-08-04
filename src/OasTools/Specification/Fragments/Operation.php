<?php

namespace DanBallance\OasTools\Specification\Fragments;

class Operation extends Fragment
{
     public function hasRequestSchema() : bool
     {
          return (bool) $this->getRequestSchemaName();
     }

     public function getRequestSchemaName() : ?string
     {
          if (isset($this->schema['requestBody'])) {
               return $this->extractSchemaName($this->schema['requestBody']);
          }
          return null;
     }

     /**
     * @TODO eventually we should be aware of the request media type here
     * but for now we will simply return the first schema we encounter
     */
     protected function extractSchemaName(array $definition)
     {
          if (!isset($definition['content'])) {
               return null;
          }
          foreach ($definition['content'] as $mediaType => $definition) {
               if (isset($definition['schema']) && isset($definition['schema']['$ref'])) {
                    $schema = $definition['schema']['$ref'];
                    $parts = explode('/', $schema);
                     return end($parts);
               }
          }
          return null;
     }

     public function hasResponseSchema() : bool
     {
          return (bool) $this->getResponseSchemaName();
     }

     public function getResponseSchemaName() : ?string
     {
          foreach ($this->getResponses(true) as $response) {
               $schemaName = $this->extractSchemaName($response);
               if ($schemaName) {
                    return $schemaName;
               }
          }
          return null;
     }

     public function getResponses($successOnly = false) : array
     {
          if (!isset($this->schema['responses'])) {
               return [];
          }
          if (!$successOnly) {
               return $this->schema['responses'];
          }
          return array_filter(
               $this->schema['responses'],
               function($response, $key) {
                    return strlen($key) == 3 && substr($key, 0, 1) == '2';
               },
               ARRAY_FILTER_USE_BOTH
          );
     }

     public function getRequestContentTypes() : array
     {
          $types = [];
          if (!isset($this->schema['requestBody'])) {
               return $types;
          }
          if (!isset($this->schema['requestBody']['content'])) {
               return $types;
          }
          $content = $this->schema['requestBody']['content'];
          foreach ($content as $type => $schema) {
               $types[] = $type;
          }
          return $types;
     }

     public function getResponseContentTypes()
     {
          $types = [];
          if (!isset($this->schema['responses'])) {
               return $types;
          }
          foreach ($this->schema['responses'] as $key => $definition) {
               if (isset($definition['content'])) {
                    foreach ($definition['content'] as $type => $schema) {
                         $types[] = $type;
                    }
               }
          }
          return $types;
     }

     public function hasRequestBody()
     {
          return isset($this->schema['requestBody']);
     }
}
