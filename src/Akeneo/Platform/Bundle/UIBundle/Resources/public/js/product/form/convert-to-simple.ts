import {EventsHash} from 'backbone';
import * as _ from 'underscore';

import BaseView = require('pimui/js/view/base');
const Dialog = require('pim/dialog');
const FormBuilder = require('pim/form-builder');
const __ = require('oro/translator');
const SecurityContext = require('pim/security-context');
const messenger = require('oro/messenger');
const Routing = require('routing');
const router = require('pim/router');
const LoadingMask = require('oro/loading-mask');

const template = require('pim/template/product/convert-to-simple');

interface Config {
    form: string;
}

class ConvertToSimple extends BaseView {
  private readonly template = _.template(template);

  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options, ...{className: 'AknDropdown-menuLink', tagName: 'button'}});

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click': this.convert,
    };
  }

  public render(): BaseView {
    if (
      SecurityContext.isGranted('pim_enrich_product_create') &&
      'product' === this.getFormData().meta.model_type &&
      // true === this.getFormData().meta.is_owner &&   TODO Check permissions
      null !== this.getFormData().parent
    ) {
      this.$el.html(this.template({
        label: 'convert TODO translate',
      }));
    }

    return BaseView.prototype.render.apply(this, arguments);
  }

  private convert() {
    return Dialog.confirm(
      'content',
      'title',
      this.doConvert.bind(this),
      'subtitle',
      'buttonClass',
      'buttonText',
    )
  }

  private doConvert() {
    const loadingMask = new LoadingMask();
    this.$el.empty()
      .append(loadingMask.render().$el.show());

    fetch(Routing.generate(this.config.url, {
      id: this.getFormData().meta.id,
    }), {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        // ...params.header,
      },
      method: 'POST',
    })
      .then((response) => {
        if (response.ok) {
          messenger.notify('success', __(this.config.trans.success));
        } else {
          messenger.notify('error', 'error');
        }
      })
      .catch((e) => {
        messenger.notify('error', e);
      })
      .finally(() => {
          loadingMask.hide().$el.remove();
          router.redirectToRoute(Routing.generate(this.config.redirectUrl));
      });
  }
}

export = ConvertToSimple;
