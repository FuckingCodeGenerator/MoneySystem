<?php

/**
 * // English
 *
 * libform is a library for PocketMine-MP for easy operation of forms
 * Copyright (c) 2018 yuko fuyutsuki < https://github.com/fuyutsuki >
 *
 * This software is distributed under "MIT license".
 * You should have received a copy of the MIT license
 * along with this program.  If not, see
 * < https://opensource.org/licenses/mit-license >.
 *
 * ---------------------------------------------------------------------
 * // 日本語
 *
 * libformは、フォームを簡単に操作するためのpocketmine-MP向けライブラリです
 * Copyright (c) 2018 yuko fuyutsuki < https://github.com/fuyutsuki >
 *
 * このソフトウェアは"MITライセンス"下で配布されています。
 * あなたはこのプログラムと共にMITライセンスのコピーを受け取ったはずです。
 * 受け取っていない場合、下記のURLからご覧ください。
 * < https://opensource.org/licenses/mit-license >
 */

namespace tokyo\pmmp\libform\element;

// libform
use tokyo\pmmp\libform\{
  form\Form
};

/**
 * InputClass
 */
class Input extends Element {

  /** @var string */
  protected const ELEMENT_NAME = "input";

  /** @var string */
  protected $placeholder = "";
  /** @var string */
  protected $defaultText = "";

  public function __construct(string $text, string $placeholder, string $defaultText = "") {
    parent::__construct($text);
    $this->placeholder = $placeholder;
    $this->defaultText = $defaultText;
  }

  final public function format(): array {
    $data = [
      Form::KEY_TYPE => self::ELEMENT_NAME,
      Form::KEY_TEXT => $this->text,
      Form::KEY_PLACEHOLDER => $this->placeholder,
      Form::KEY_DEFAULT => $this->defaultText
    ];
    return $data;
  }
}
