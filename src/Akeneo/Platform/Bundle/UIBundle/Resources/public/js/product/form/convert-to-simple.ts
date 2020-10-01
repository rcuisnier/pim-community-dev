import {EventsHash} from 'backbone';
import * as _ from 'underscore';

import BaseView = require('pimui/js/view/base');
const Dialog = require('pim/dialog');
const __ = require('oro/translator');
const SecurityContext = require('pim/security-context');
const messenger = require('oro/messenger');
const Routing = require('routing');
const router = require('pim/router');

const template = require('pim/template/product/convert-to-simple');

interface Config {
    url: string;
    successMessage: string;
}

class ConvertToSimple extends BaseView {
    private readonly template = _.template(template);

    private readonly config: Config;

    constructor(options: { config: Config }) {
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
            () => {
                router.showLoadingMask();

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
                            messenger.notify('success', __(this.config.successMessage));
                        } else {
                            messenger.notify('error', 'error TODO');
                        }
                    })
                    .catch((e) => {
                        console.log(e);
                    })
                    .finally(() => {
                        router.hideLoadingMask();
                        router.reloadPage();
                    });
            },
            'subtitle',
            'buttonClass',
            'buttonText',
        )
    }
}

export = ConvertToSimple;
