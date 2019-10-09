<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Ast\TypeSystem\Definition;

use Railt\Ast\TypeSystem\TypeDefinitionNode;
use Railt\Ast\Generic\FieldDefinitionCollection;
use Railt\Ast\Generic\InterfaceTypeDefinitionCollection;

/**
 * Class ObjectTypeDefinitionNode
 *
 * <code>
 *  export type ObjectTypeDefinitionNode = {
 *      +kind: 'ObjectTypeDefinition',
 *      +loc?: Location,
 *      +description?: StringValueNode,
 *      +name: NameNode,
 *      +interfaces?: $ReadOnlyArray<NamedTypeNode>,
 *      +directives?: $ReadOnlyArray<DirectiveNode>,
 *      +fields?: $ReadOnlyArray<FieldDefinitionNode>,
 *      ...
 *  };
 * </code>
 *
 * @see https://github.com/graphql/graphql-js/blob/v14.5.7/src/language/ast.js#L453
 */
class ObjectTypeDefinitionNode extends TypeDefinitionNode
{
    /**
     * @var InterfaceTypeDefinitionCollection|null
     */
    public ?InterfaceTypeDefinitionCollection $interfaces = null;

    /**
     * @var FieldDefinitionCollection|null
     */
    public ?FieldDefinitionCollection $fields = null;
}
