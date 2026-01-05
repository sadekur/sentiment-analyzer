// Elements
import PageNumbers from "./elements/PageNumbers";
import Previous from "./elements/Previous";
import Next from "./elements/Next";

const Pagination = ({ baseSlug = "dashboard", current = 1, total = 1, assets = "" }) => {
    return (
        <div className="flex justify-center items-center gap-[6px] my-10">
            <Previous baseSlug={baseSlug} current={current} assets={assets} />

            <PageNumbers total={total} current={current} baseSlug={baseSlug} />

            <Next baseSlug={baseSlug} current={current} total={total} assets={assets} />
        </div>
    );
};

export default Pagination;
